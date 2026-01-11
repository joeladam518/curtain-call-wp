import {Button, ComboboxControl, Notice, SelectControl, Spinner, TextControl} from '@wordpress/components';
import {useSelect} from '@wordpress/data';
import {store as coreStore} from '@wordpress/core-data';
import {PluginDocumentSettingPanel, store as editorStore} from '@wordpress/editor';
import {__} from '@wordpress/i18n';
import {FC, useMemo, useState} from 'react';
import MemberType from '../enums/MemberType';
import PostType from '../enums/PostType';
import {TEXT_DOMAIN} from '../utils/constants';
import relationsStore, {
    type RelationsStoreSelectors,
    useDispatch as useRelationsDispatch,
} from '../stores/relations-store';
import {CastCrewEntity} from '../types/cast-and-crew';
import {ProductionEntity} from '../types/production';

type SidebarState = {
    targetId: string | number;
    type: MemberType;
    role: string;
    order: string;
    successMessage: string | null;
};

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
        targetId: id || '',
    }));
    const setType = (type: MemberType) => setState(current => ({...current, type}));
    const setRole = (role: string) => setState(current => ({...current, role}));
    const setOrder = (order: string) => setState(current => ({...current, order}));
    const setSuccessMessage = (message: string | null) => setState(current => ({...current, successMessage: message}));

    const postId = useSelect(select => select(editorStore).getCurrentPostId(), []) as string | number | null;
    const postType = useSelect(select => select(editorStore).getCurrentPostType(), []) as PostType | null;
    const relations = useSelect(select => (select(relationsStore) as RelationsStoreSelectors).getRelations(), []);
    const isFetching = useSelect(select => (select(relationsStore) as RelationsStoreSelectors).isLoading(), []);
    const fetchingError = useSelect(select => (select(relationsStore) as RelationsStoreSelectors).getError(), []);
    const {attachCastCrew, attachProduction} = useRelationsDispatch();
    const fetchPostType = useMemo(
        () => {
            if (postType === PostType.CastCrew) {
                return PostType.Production;
            }

            if (postType === PostType.Production) {
                return PostType.CastCrew;
            }

            return undefined;
        },
        [postType]
    );
    const records = useSelect(
        select => select(coreStore).getEntityRecords(
            'postType',
            fetchPostType as string,
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
                        value: String(record.id),
                    });
                });
            }

            if (fetchPostType === PostType.Production) {
                return (records as ProductionEntity[]).map((record: ProductionEntity) => ({
                    label: record?.meta?._ccwp_production_name || record?.title?.rendered || `#${record.id}`,
                    value: String(record.id),
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
                    castcrewId: postId as string | number,
                    type: state.type,
                    role: state.role,
                    customOrder: parseInt(state.order, 10) || 0,
                });
            } else if (postType === PostType.Production) {
                await attachCastCrew({
                    productionId: postId as string | number,
                    castcrewId: state.targetId,
                    type: state.type,
                    role: state.role,
                    customOrder: parseInt(state.order, 10) || 0,
                });
            } else {
                console.error(`Unsupported post type - "${postType}"`);
            }

            setTargetId('');
            setRole('');
            setOrder('');
            setSuccessMessage(__('Successfully attached!', TEXT_DOMAIN));
            setTimeout(() => setSuccessMessage(null), 3000);
        } catch (e) {
            console.error('Error attaching cast crew:', e);
        }
    };

    const title = postType === PostType.Production
        ? __('Attach Cast & Crew', TEXT_DOMAIN)
        : __('Attach Productions', TEXT_DOMAIN);
    const targetLabel = postType === PostType.Production
        ? __('Cast/Crew Member', TEXT_DOMAIN)
        : __('Production', TEXT_DOMAIN);

    return (
        <PluginDocumentSettingPanel
            name="ccwp-sidebar-attach"
            title={title}
            // @ts-expect-error TS2322
            initialOpen={true}
        >
            <div
                style={{
                    display: 'flex',
                    flexDirection: 'column',
                    alignItems: 'stretch',
                    justifyContent: 'flex-start',
                    gap: '12px',
                }}
            >
                {!!fetchingError && (
                    <Notice
                        status="error"
                        isDismissible={false}
                    >
                        {fetchingError || ''}
                    </Notice>
                )}

                {!!state.successMessage && (
                    <Notice
                        status="success"
                        isDismissible={false}
                    >
                        {state.successMessage}
                    </Notice>
                )}

                {isFetching && <Spinner />}

                <div
                    style={{
                        display: 'flex',
                        flexDirection: 'column',
                        alignItems: 'stretch',
                        justifyContent: 'flex-start',
                        gap: '15px',
                        marginBottom: '12px',
                    }}
                >
                    <p style={{marginBottom: '16px', color: '#757575'}}>
                        {__('Currently attached:', TEXT_DOMAIN)} <strong>{relations.length}</strong>
                    </p>

                    <SelectControl<MemberType>
                        __next40pxDefaultSize
                        __nextHasNoMarginBottom
                        label={__('Type', TEXT_DOMAIN)}
                        value={state.type}
                        options={[
                            {label: __('Cast', TEXT_DOMAIN), value: MemberType.Cast},
                            {label: __('Crew', TEXT_DOMAIN), value: MemberType.Crew},
                        ]}
                        onChange={type => setType(type as MemberType)}
                    />

                    <ComboboxControl
                        __next40pxDefaultSize
                        __nextHasNoMarginBottom
                        label={targetLabel}
                        value={state.targetId.toString()}
                        options={[{label: __('Select…', TEXT_DOMAIN), value: ''}, ...options]}
                        onChange={(val: string | null | undefined) => setTargetId(val || '')}
                        help={__('Search and select to attach', TEXT_DOMAIN)}
                    />

                    <TextControl
                        __next40pxDefaultSize
                        __nextHasNoMarginBottom
                        label={__('Role', TEXT_DOMAIN)}
                        value={state.role}
                        onChange={setRole}
                        placeholder={__('e.g. Hamlet, Director', TEXT_DOMAIN)}
                    />

                    <TextControl
                        __next40pxDefaultSize
                        __nextHasNoMarginBottom
                        label={__('Order', TEXT_DOMAIN)}
                        type="number"
                        value={state.order}
                        onChange={setOrder}
                        placeholder="0"
                    />

                    <Button
                        __next40pxDefaultSize
                        variant="primary"
                        onClick={handleAttach}
                        disabled={!state.targetId || isFetching}
                        style={{width: '100%'}}
                    >
                        {__('Attach', TEXT_DOMAIN)}
                    </Button>
                </div>

                <p
                    style={{
                        fontSize: '12px',
                        color: '#757575',
                        marginTop: '16px',
                        paddingTop: '16px',
                        borderTop: '1px solid #ddd',
                    }}
                >
                    {__('View and edit attached items in the drawer below the editor.', TEXT_DOMAIN)}
                </p>
            </div>
        </PluginDocumentSettingPanel>
    );
};

Sidebar.displayName = 'Sidebar';

export default Sidebar;
