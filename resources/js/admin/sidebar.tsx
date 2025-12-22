import React, {FC, useEffect, useState} from 'react'
import {registerPlugin} from '@wordpress/plugins';
import {PluginSidebar} from '@wordpress/editor';
import {PanelBody, Button, SelectControl, TextControl, Spinner, Notice} from '@wordpress/components';
import {useSelect} from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';

declare const CCWP_SETTINGS: {
    nonce: string;
};

type Relation = {
    production_id: number;
    cast_and_crew_id: number;
    type: 'cast' | 'crew';
    role?: string;
    custom_order?: number | null;
};

const Sidebar: FC = () => {
    const [loading, setLoading] = useState(false);
    const [relations, setRelations] = useState<Relation[]>([]);
    const [error, setError] = useState<string | null>(null);
    const [newType, setNewType] = useState<'cast'|'crew'>('cast');
    const [newTargetId, setNewTargetId] = useState<string>('');
    const [newRole, setNewRole] = useState<string>('');
    const [newOrder, setNewOrder] = useState<string>('');

    // Determine current post and post type from the editor
    const postId = useSelect((select) => select('core/editor').getCurrentPostId(), []);
    const postType = useSelect((select) => select('core/editor').getCurrentPostType(), []);

    console.log('postId', postId);
    console.log('postType', postType);

    const isProduction = postType === 'ccwp_production';
    const isCastCrew = postType === 'ccwp_cast_and_crew';

    // Load searchable options from core store
    const options = useSelect((select) => {
        const core = select('core');
        if (isProduction) {
            // Get cast & crew posts
            const records = core.getEntityRecords('postType', 'ccwp_cast_and_crew', { per_page: 50, _fields: ['id','title','meta'] }) || [];
            return (records as any[]).map((r: any) => ({ label: r.title?.rendered || `#${r.id}`, value: String(r.id) }));
        }
        if (isCastCrew) {
            const records = core.getEntityRecords('postType', 'ccwp_production', { per_page: 50, _fields: ['id','title','meta'] }) || [];
            return (records as any[]).map((r: any) => ({ label: r.title?.rendered || `#${r.id}`, value: String(r.id) }));
        }
        return [];
    }, [postType, isProduction, isCastCrew]);

    console.log('options', options);

    const attach = async () => {
        if (!newTargetId) return;
        const body: any = isProduction
            ? { production_id: postId, cast_and_crew_id: Number(newTargetId), type: newType, role: newRole, custom_order: newOrder ? Number(newOrder) : undefined }
            : { production_id: Number(newTargetId), cast_and_crew_id: postId, type: newType, role: newRole, custom_order: newOrder ? Number(newOrder) : undefined };
        setLoading(true);
        setError(null);
        try {
            await apiFetch({ path: `/ccwp/v1/relations`, method: 'POST', data: body, headers: { 'X-WP-Nonce': CCWP_SETTINGS?.nonce } } as any);
            // reload
            const query: any = {};
            if (isProduction) query.production_id = postId;
            if (isCastCrew) query.cast_and_crew_id = postId;
            const rows: any = await apiFetch({ path: `/ccwp/v1/relations?` + new URLSearchParams(query as any).toString(), headers: { 'X-WP-Nonce': CCWP_SETTINGS?.nonce } } as any);
            setRelations(rows || []);
            setNewTargetId(''); setNewRole(''); setNewOrder('');
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
            const qs = new URLSearchParams({ production_id: String(r.production_id), cast_and_crew_id: String(r.cast_and_crew_id) }).toString();
            await apiFetch({ path: `/ccwp/v1/relations?${qs}`, method: 'DELETE', headers: { 'X-WP-Nonce': CCWP_SETTINGS?.nonce } } as any);
            setRelations(relations.filter(x => !(x.production_id === r.production_id && x.cast_and_crew_id === r.cast_and_crew_id)));
        } catch (e: any) {
            setError(e?.message || 'Failed to detach');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        if (!postId) return;
        setLoading(true);
        setError(null);

        const query: any = {};
        if (isProduction) query.production_id = postId;
        if (isCastCrew) query.cast_and_crew_id = postId;

        apiFetch({ path: `/ccwp/v1/relations?` + new URLSearchParams(query as any).toString(), headers: { 'X-WP-Nonce': CCWP_SETTINGS?.nonce } } as any)
            .then((rows: Relation[]) => setRelations(rows || []))
            .catch((e: any) => setError(e?.message || 'Failed to load relations'))
            .finally(() => setLoading(false));
    }, [postId, postType]);

    const title = isProduction ? 'Production Cast & Crew' : 'Attached Productions';



    return (
        <PluginSidebar name="ccwp-sidebar" title={title} icon="admin-users">
            <PanelBody title={title} initialOpen>
                {!!error && <Notice status="error" isDismissible={false}>{error}</Notice>}
                {loading ? <Spinner /> : (
                    <div>
                        <div style={{ marginBottom: 12 }}>
                            <SelectControl
                                label="Type"
                                value={newType}
                                options={[{ label: 'Cast', value: 'cast' }, { label: 'Crew', value: 'crew' }]}
                                onChange={(v: any) => setNewType(v)}
                            />
                            <SelectControl
                                label={isProduction ? 'Cast/Crew' : 'Production'}
                                value={newTargetId}
                                options={[{ label: 'Select…', value: '' }, ...options]}
                                onChange={(v: any) => setNewTargetId(v)}
                                help="Search and select an existing post to attach"
                            />
                            <TextControl label="Role" value={newRole} onChange={setNewRole} />
                            <TextControl label="Custom Order" type="number" value={newOrder} onChange={setNewOrder} />
                            <Button isPrimary onClick={attach} disabled={!newTargetId}>Attach</Button>
                        </div>
                        <div>
                            {relations.length === 0 && <p>No relations yet.</p>}
                            {relations.map((r) => (
                                <div
                                    key={`${r.production_id}-${r.cast_and_crew_id}-${r.type}`}
                                    style={{ display: 'grid', gridTemplateColumns: 'auto 1fr auto', gap: 8, alignItems: 'center', marginBottom: 8 }}
                                >
                                    <code>{r.type.toUpperCase()}</code>
                                    <div>
                                        <div>Production: {r.production_id} | Cast/Crew: {r.cast_and_crew_id}</div>
                                        <div style={{ display: 'flex', gap: 8, marginTop: 6 }}>
                                            <TextControl
                                                label="Role"
                                                value={r.role || ''}
                                                onChange={(val: string) => setRelations(relations.map(x => x === r ? { ...x, role: val } : x))}
                                            />
                                            <TextControl
                                                label="Order"
                                                type="number"
                                                value={typeof r.custom_order === 'number' ? String(r.custom_order) : ''}
                                                onChange={(val: string) => setRelations(relations.map(x => x === r ? { ...x, custom_order: val === '' ? null : Number(val) } : x))}
                                            />
                                            <Button
                                                onClick={async () => {
                                                    // Persist inline edits via attach upsert
                                                    await apiFetch({
                                                        path: `/ccwp/v1/relations`,
                                                        method: 'POST',
                                                        data: {
                                                            production_id: r.production_id,
                                                            cast_and_crew_id: r.cast_and_crew_id,
                                                            type: r.type,
                                                            role: r.role,
                                                            custom_order: r.custom_order,
                                                        },
                                                        headers: { 'X-WP-Nonce': CCWP_SETTINGS?.nonce },
                                                    } as any);
                                                }}
                                            >Save</Button>
                                        </div>
                                    </div>
                                    <Button isSecondary isDestructive onClick={() => detach(r)}>Remove</Button>
                                </div>
                            ))}
                        </div>
                    </div>
                )}
            </PanelBody>
        </PluginSidebar>
    );
};

registerPlugin('ccwp-sidebar', { icon: 'admin-users', render: Sidebar });
