import {Button, ComboboxControl, Notice, SelectControl, Spinner, TextControl} from '@wordpress/components';
import {useDispatch, useSelect} from '@wordpress/data';
import {PluginSidebar, store as editorStore} from '@wordpress/editor';
import {FC, useEffect, useMemo, useState} from 'react';
import CastCrewData from '../data/CastCrewData';
import ProductionData from '../data/ProductionData';
import MemberType from '../enums/MemberType';
import PostType from '../enums/PostType';
import Icon from '../icons/TheatreCurtains';
import {STORE_NAME} from '../stores/relations-store';
import {CastCrewEntity} from '../types/cast-and-crew';
import {ProductionEntity} from '../types/production';

type SidebarState = {
    targetId: string | number;
    type: MemberType;
    role: string;
    order: string;
    successMessage: string | null;
}

const Sidebar: FC = () => {
    const [state, setState] = useState<SidebarState>({
        targetId: '',
        type: MemberType.Cast,
        role: '',
        order: '0',
        successMessage: null,
    });
    const setTargetId = (id: string | number | null | undefined) => setState(current => ({
        ...current,
        targetId: id || ''
    }));
    const setType = (type: MemberType) => setState(current => ({...current, type}));
    const setRole = (role: string) => setState(current => ({...current, role}));
    const setOrder = (order: string) => setState(current => ({...current, order}));
    const setSuccessMessage = (message: string | null) => setState(current => ({...current, successMessage: message}));

    const postId: string | number = useSelect(select => select(editorStore).getCurrentPostId(), []);
    const postType: PostType = useSelect(select => select(editorStore).getCurrentPostType(), []);
    const {relations, loading, error} = useSelect(select => {
        const store = select(STORE_NAME);
        return {
            relations: store.getRelations() as ((CastCrewData[]) | (ProductionData[])),
            loading: store.isLoading() as boolean,
            error: store.getError() as string | null,
        };
    }, []);
    const {attachCastCrew, attachProduction, fetchCastCrew, fetchProductions} = useDispatch(STORE_NAME);
    const fetchPostType = useMemo(
        () => {
            if (postType === PostType.CastCrew) {
                return PostType.Production
            }

            if (postType === PostType.Production) {
                return PostType.CastCrew
            }

            return undefined;
        },
        [postType]
    );
    const records = useSelect(
        select => select('core').getEntityRecords(
            'postType',
            fetchPostType,
            {per_page: -1, _fields: ['id', 'title', 'meta']}
        ) || [],
        [fetchPostType]
    );
    const options = useMemo(
        () => {
            if (fetchPostType === PostType.CastCrew) {
                return (records as CastCrewEntity[]).map((record: CastCrewEntity) => {
                    const firstName = record?.meta?._ccwp_cast_crew_name_first || null;
                    const lastName = record?.meta?._ccwp_cast_crew_name_last || null;
                    return ({
                        label: (
                            `${firstName || ''} ${lastName || ''}`.trim() ||
                            record.title?.rendered ||
                            `#${record.id}`
                        ),
                        value: String(record.id)
                    });
                });
            }

            if (fetchPostType === PostType.Production) {
                return (records as ProductionEntity[]).map((record: ProductionEntity) => ({
                    label: record?.meta?._ccwp_production_name || record?.title?.rendered || `#${record.id}`,
                    value: String(record.id)
                }));
            }

            return [];
        },
        [records, fetchPostType]
    );

    const handleAttach = async () => {
        if (!postId || !postType || !state.targetId) {
            return;
        }

        setSuccessMessage(null);

        try {
            if (postType === PostType.CastCrew) {
                await attachProduction({
                    productionId: state.targetId,
                    castcrewId: postId,
                    type: state.type,
                    role: state.role,
                    customOrder: parseInt(state.order, 10) || 0
                });
            } else if (postType === PostType.Production) {
                await attachCastCrew({
                    productionId: postId,
                    castcrewId: state.targetId,
                    type: state.type,
                    role: state.role,
                    customOrder: parseInt(state.order, 10) || 0
                });
            } else {
                console.error(`Unsupported post type - "${postType}"`);
            }

            setTargetId('');
            setRole('');
            setOrder('');
            setSuccessMessage('Successfully attached!');
            setTimeout(() => setSuccessMessage(null), 3000);
        } catch (e) {
            console.error('Error attaching cast crew:', e);
        }
    };

    const title = postType === PostType.Production ? 'Attach Cast & Crew' : 'Attach to Production';
    const targetLabel = postType === PostType.Production ? 'Cast/Crew Member' : 'Production';

    return (
        <PluginSidebar
            name="ccwp-sidebar-attach"
            title={title}
            icon={<Icon /> as any}
        >
            <div
                style={{
                    display: 'flex',
                    flexDirection: 'column',
                    alignItems: 'stretch',
                    justifyContent: 'flex-start',
                    gap: '15px',
                    padding: '16px',
                }}
            >
                {!!error && (
                    <Notice status="error" isDismissible={false} style={{marginBottom: '16px'}}>
                        {error || ''}
                    </Notice>
                )}

                {!!state.successMessage && (
                    <Notice status="success" isDismissible={false} style={{marginBottom: '16px'}}>
                        {state.successMessage}
                    </Notice>
                )}

                {loading && <Spinner />}

                <div
                    style={{
                        display: 'flex',
                        flexDirection: 'column',
                        alignItems: 'stretch',
                        justifyContent: 'flex-start',
                        gap: '15px',
                        marginBottom: '12px'
                    }}
                >
                    <p style={{marginBottom: '16px', color: '#757575'}}>
                        Currently attached: <strong>{relations.length}</strong>
                    </p>

                    <SelectControl<MemberType>
                        __next40pxDefaultSize
                        __nextHasNoMarginBottom
                        label="Type"
                        value={state.type}
                        options={[
                            {label: 'Cast', value: MemberType.Cast},
                            {label: 'Crew', value: MemberType.Crew},
                        ]}
                        onChange={setType}
                    />

                    <ComboboxControl
                        __next40pxDefaultSize
                        __nextHasNoMarginBottom
                        label={targetLabel}
                        value={state.targetId.toString()}
                        options={[{label: 'Select…', value: ''}, ...options]}
                        onChange={(val: string | null | undefined) => setTargetId(val || '')}
                        help="Search and select to attach"
                    />

                    <TextControl
                        __next40pxDefaultSize
                        __nextHasNoMarginBottom
                        label="Role"
                        value={state.role}
                        onChange={setRole}
                        placeholder="e.g. Hamlet, Director"
                    />

                    <TextControl
                        __next40pxDefaultSize
                        __nextHasNoMarginBottom
                        label="Order"
                        type="number"
                        value={state.order}
                        onChange={setOrder}
                        placeholder="0"
                    />

                    <Button
                        variant="primary"
                        onClick={handleAttach}
                        disabled={!state.targetId || loading}
                        style={{width: '100%'}}
                    >
                        Attach
                    </Button>
                </div>

                <p style={{fontSize: '12px', color: '#757575', marginTop: '16px', paddingTop: '16px', borderTop: '1px solid #ddd'}}>
                    View and edit attached items in the drawer below the editor.
                </p>
            </div>
        </PluginSidebar>
    );
};

Sidebar.displayName = 'Sidebar';

export default Sidebar;
