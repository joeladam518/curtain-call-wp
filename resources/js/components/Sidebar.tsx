import {FC, useEffect, useMemo, useState} from 'react';
import apiFetch from '@wordpress/api-fetch';
import {Button, Notice, ComboboxControl, SelectControl, Spinner, TextControl} from '@wordpress/components';
import {useSelect} from '@wordpress/data';
import {PluginSidebar, store as editorStore} from '@wordpress/editor';
import MemberType from '../enums/MemberType';
import Icon from '../icons/TheatreCurtains';
import {EditorSelectors} from '../types/stores';

type Relation = {
    production_id: number;
    cast_and_crew_id: number;
    type: MemberType;
    role?: string;
    custom_order?: number | null;
};

const Sidebar: FC = () => {
    const [loading, setLoading] = useState(false);
    const [relations, setRelations] = useState<Relation[]>([]);
    const [error, setError] = useState<string | null>(null);
    const [newType, setNewType] = useState<MemberType>(MemberType.Cast);
    const [newTargetId, setNewTargetId] = useState<string>('');
    const [newRole, setNewRole] = useState<string>('');
    const [newOrder, setNewOrder] = useState<string>('');

    // Determine the current post and post type from the editor
    const postId = useSelect(select => (select(editorStore) as EditorSelectors).getCurrentPostId(), []);
    const postType = useSelect(select => (select(editorStore) as EditorSelectors).getCurrentPostType(), []);
    const isProduction = postType === 'ccwp_production';
    const isCastCrew = postType === 'ccwp_cast_and_crew';

    const core = useSelect(select => select('core'), []);
    const options = useMemo(() => {
        if (isProduction) {
            // Get cast & crew posts
            const records = core.getEntityRecords(
                'postType',
                'ccwp_cast_and_crew',
                {per_page: 50, _fields: ['id', 'title', 'meta']}
            ) || [];
            return (records as any[]).map((r: any) => ({label: r.title?.rendered || `#${r.id}`, value: String(r.id)}));
        }

        if (isCastCrew) {
            const records = core.getEntityRecords(
                'postType',
                'ccwp_production',
                {per_page: 50, _fields: ['id', 'title', 'meta']}
            ) || [];
            return (records as any[]).map((r: any) => ({label: r.title?.rendered || `#${r.id}`, value: String(r.id)}));
        }

        return [];
    }, [core, isProduction, isCastCrew]);

    const attach = async () => {
        if (!postId || !newTargetId) {
            return;
        }
        const body: any = isProduction
            ? {
                production_id: postId,
                cast_and_crew_id: Number(newTargetId),
                type: newType,
                role: newRole,
                custom_order: newOrder ? Number(newOrder) : undefined,
            }
            : {
                production_id: Number(newTargetId),
                cast_and_crew_id: postId,
                type: newType,
                role: newRole,
                custom_order: newOrder ? Number(newOrder) : undefined,
            };
        setLoading(true);
        setError(null);
        try {
            await apiFetch({
                path: '/ccwp/v1/relations',
                method: 'POST',
                data: body,
                headers: {'X-WP-Nonce': window.CCWP_SETTINGS?.nonce ?? ''},
            });
            // reload
            const query: Record<string, string> = {};
            if (isProduction) {
                query.production_id = postId.toString();
            }
            if (isCastCrew) {
                query.cast_and_crew_id = postId.toString();
            }
            const rows: any = await apiFetch({
                path: '/ccwp/v1/relations?' + new URLSearchParams(query).toString(),
                headers: {'X-WP-Nonce': window.CCWP_SETTINGS?.nonce ?? ''},
            });
            setRelations(rows || []);
            setNewTargetId('');
            setNewRole('');
            setNewOrder('');
        } catch (e: any) {
            setError(e?.message || 'Failed to attach');
        } finally {
            setLoading(false);
        }
    };

    const detach = async (r: Relation) => {
        setLoading(true);
        setError(null);
        try {
            const qs = new URLSearchParams({
                production_id: String(r.production_id),
                cast_and_crew_id: String(r.cast_and_crew_id),
            }).toString();
            await apiFetch({
                path: `/ccwp/v1/relations?${qs}`,
                method: 'DELETE',
                headers: {'X-WP-Nonce': window.CCWP_SETTINGS?.nonce ?? ''},
            });
            setRelations(
                relations.filter(
                    x => !(x.production_id === r.production_id && x.cast_and_crew_id === r.cast_and_crew_id)
                )
            );
        } catch (e: any) {
            setError(e?.message || 'Failed to detach');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        if (!postId) {
            return;
        }

        setLoading(true);
        setError(null);

        const query: Record<string, string> = {};
        if (isProduction) {
            query.production_id = postId.toString();
        }
        if (isCastCrew) {
            query.cast_and_crew_id = postId.toString();
        }

        apiFetch({
            path: '/ccwp/v1/relations?' + new URLSearchParams(query).toString(),
            headers: {'X-WP-Nonce': window.CCWP_SETTINGS?.nonce ?? ''},
        })
            .then((value: unknown) => setRelations((value || []) as Relation[]))
            .catch((e: any) => setError(e?.message || 'Failed to load relations'))
            .finally(() => setLoading(false));
    }, [isCastCrew, isProduction, postId, postType]);

    const title = isProduction ? 'Production Cast & Crew' : 'Attached Productions';

    return (
        <PluginSidebar
            name="ccwp-sidebar"
            title={title}
            icon={<Icon /> as any}
        >
            <div>
                {!!error && <Notice status="error" isDismissible={false}>{error}</Notice>}
                {loading ? <Spinner /> : (
                    <div>
                        <div style={{marginBottom: 12}}>
                            <SelectControl<MemberType>
                                label="Type"
                                value={newType}
                                options={[
                                    {label: 'Cast', value: MemberType.Cast},
                                    {label: 'Crew', value: MemberType.Crew},
                                ]}
                                onChange={(v: MemberType) => setNewType(v)}
                            />
                            <ComboboxControl
                                __next40pxDefaultSize
                                __nextHasNoMarginBottom
                                label={isProduction ? 'Cast/Crew' : 'Production'}
                                value={newTargetId}
                                options={[{label: 'Select…', value: ''}, ...options]}
                                onChange={(v: string) => setNewTargetId(v)}
                                help="Search and select an existing post to attach"
                            />
                            <TextControl
                                __next40pxDefaultSize
                                __nextHasNoMarginBottom
                                label="Role"
                                value={newRole}
                                onChange={setNewRole}
                            />
                            <TextControl
                                __next40pxDefaultSize
                                __nextHasNoMarginBottom
                                label="Custom Order"
                                type="number"
                                value={newOrder}
                                onChange={setNewOrder}
                            />
                            <Button
                                variant="primary"
                                onClick={attach}
                                disabled={!newTargetId}
                            >Attach</Button>
                        </div>
                        <div>
                            {relations.length === 0 && <p>No relations yet.</p>}
                            {relations.map(r => (
                                <div
                                    key={`${r.production_id}-${r.cast_and_crew_id}-${r.type}`}
                                    style={{
                                        display: 'grid',
                                        gridTemplateColumns: 'auto 1fr auto',
                                        gap: 8,
                                        alignItems: 'center',
                                        marginBottom: 8,
                                    }}
                                >
                                    <code>{r.type.toUpperCase()}</code>
                                    <div>
                                        <div>Production: {r.production_id} | Cast/Crew: {r.cast_and_crew_id}</div>
                                        <div style={{display: 'flex', gap: 8, marginTop: 6}}>
                                            <TextControl
                                                __next40pxDefaultSize
                                                __nextHasNoMarginBottom
                                                label="Role"
                                                value={r.role || ''}
                                                onChange={(val: string) => setRelations(
                                                    relations.map(x => x === r ? {...x, role: val} : x)
                                                )}
                                            />
                                            <TextControl
                                                __next40pxDefaultSize
                                                __nextHasNoMarginBottom
                                                label="Order"
                                                type="number"
                                                value={typeof r.custom_order === 'number' ? String(r.custom_order) : ''}
                                                onChange={(val: string) => setRelations(
                                                    relations.map(
                                                        x => x === r
                                                            ? {...x, custom_order: val === '' ? null : Number(val)}
                                                            : x
                                                    )
                                                )}
                                            />
                                            <Button
                                                onClick={async () => {
                                                    // Persist inline edits via attach upsert
                                                    await apiFetch({
                                                        path: '/ccwp/v1/relations',
                                                        method: 'POST',
                                                        data: {
                                                            production_id: r.production_id,
                                                            cast_and_crew_id: r.cast_and_crew_id,
                                                            type: r.type,
                                                            role: r.role,
                                                            custom_order: r.custom_order,
                                                        },
                                                        headers: {'X-WP-Nonce': window.CCWP_SETTINGS?.nonce ?? ''},
                                                    });
                                                }}
                                            >Save</Button>
                                        </div>
                                    </div>
                                    <Button
                                        variant="secondary"
                                        isDestructive
                                        onClick={() => detach(r)}
                                    >Remove</Button>
                                </div>
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </PluginSidebar>
    );
};

export default Sidebar;
