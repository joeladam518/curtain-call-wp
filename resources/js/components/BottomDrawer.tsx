import {type FC, useState, useEffect, useRef} from 'react';
import {createPortal} from 'react-dom';
import {useMediaQuery} from '@wordpress/compose';
import {__} from '@wordpress/i18n';
import {useRelations, Relation} from '../hooks/useRelations';
import MemberType from '../enums/MemberType';
import {Button, Spinner, TextControl, Notice, Tooltip} from '@wordpress/components';
import ChevronUp from '../icons/ChevronUp';
import ChevronDown from '../icons/ChevronDown';

const DrawerContent: FC = () => {
    const {relations, loading, error, update, detach, isProduction, isCastCrew} = useRelations();
    const [isCollapsed, setIsCollapsed] = useState(false);
    const [editingRelations, setEditingRelations] = useState<Record<string, Relation>>({});
    const [savingId, setSavingId] = useState<string | null>(null);
    const [drawerHeight, setDrawerHeight] = useState(400);
    const drawerRef = useRef<HTMLDivElement | null>(null);
    const resizeHandleRef = useRef<HTMLButtonElement | null>(null);
    const isResizingRef = useRef(false);
    const startYRef = useRef(0);
    const startHeightRef = useRef(0);

    const [{min, max}, setHeightConstraints] = useState(() => ({}));

    const getRelationKey = (r: Relation) => `${r.production_id}-${r.cast_and_crew_id}-${r.type}`;
    const getAriaValueNow = ( height ) => Math.round(((height - min) / (max - min)) * 100);
    const usedAriaValueNow = getAriaValueNow(drawerHeight);
    const isShort = useMediaQuery('(max-height: 549px)');

    const handleFieldChange = (relation: Relation, field: 'role' | 'custom_order', value: string) => {
        const key = getRelationKey(relation);
        const updated = editingRelations[key] || relation;
        setEditingRelations({
            ...editingRelations,
            [key]: {
                ...updated,
                [field]: field === 'custom_order'
                    ? (value === '' ? null : Number(value))
                    : value
            }
        });
    };

    const handleSave = async (relation: Relation) => {
        const key = getRelationKey(relation);
        const toSave = editingRelations[key] || relation;

        setSavingId(key);
        try {
            await update(toSave);
            // Remove from editing state after successful save
            const {[key]: _, ...rest} = editingRelations;
            setEditingRelations(rest);
        } catch (e) {
            // Error handled by hook
        } finally {
            setSavingId(null);
        }
    };

    const handleDetach = async (relation: Relation) => {
        if (confirm('Are you sure you want to detach this relation?')) {
            await detach(relation);
        }
    };

    const getCurrentRelation = (r: Relation) => {
        const key = getRelationKey(r);
        return editingRelations[key] || r;
    };

    const hasChanges = (r: Relation) => {
        const key = getRelationKey(r);
        return key in editingRelations;
    };

    const castRelations = relations.filter(r => r.type === MemberType.Cast);
    const crewRelations = relations.filter(r => r.type === MemberType.Crew);
    const title = isProduction ? 'Cast & Crew' : 'Productions';

    // Handle resize
    useEffect(() => {
        const handleMouseDown = (e: MouseEvent) => {
            if (resizeHandleRef.current && !resizeHandleRef.current.contains(e.target as Node)) return;

            isResizingRef.current = true;
            startYRef.current = e.clientY;
            startHeightRef.current = drawerHeight;

            e.preventDefault();
            document.body.style.cursor = 'ns-resize';
            document.body.style.userSelect = 'none';
        };

        const handleMouseMove = (e: MouseEvent) => {
            if (!isResizingRef.current) return;

            const deltaY = startYRef.current - e.clientY;
            const newHeight = Math.min(Math.max(startHeightRef.current + deltaY, 200), 800);
            setDrawerHeight(newHeight);
        };

        const handleMouseUp = () => {
            if (isResizingRef.current) {
                isResizingRef.current = false;
                document.body.style.cursor = '';
                document.body.style.userSelect = '';
            }
        };

        document.addEventListener('mousedown', handleMouseDown);
        document.addEventListener('mousemove', handleMouseMove);
        document.addEventListener('mouseup', handleMouseUp);

        return () => {
            document.removeEventListener('mousedown', handleMouseDown);
            document.removeEventListener('mousemove', handleMouseMove);
            document.removeEventListener('mouseup', handleMouseUp);
        };
    }, [drawerHeight]);

    return (
        <div
            ref={drawerRef}
            className={`ccwp-bottom-drawer${isCollapsed ? ' closed' : ''}`}
            style={{height: isCollapsed ? 'auto' : `${drawerHeight}px`}}
        >
            <div className="bottom-drawer-header">
                <Tooltip text={__('Drag to resize')}>
                    <button // eslint-disable-line jsx-a11y/role-supports-aria-props
                        className="bottom-drawer-separator"
                        ref={resizeHandleRef}
                        role="separator" // eslint-disable-line jsx-a11y/no-interactive-element-to-noninteractive-role
                        aria-valuenow={usedAriaValueNow}
                        aria-label={__('Drag to resize')}
                    />
                </Tooltip>
                <div className="bottom-drawer-title">
                    <span>{title}</span>
                    {relations.length > 0 && (
                        <span className="ccwp-drawer-count-badge">
                            {relations.length}
                        </span>
                    )}
                </div>
                <button
                    type="button"
                    className="drawer-toggle"
                    onClick={() => setIsCollapsed(!isCollapsed)}
                    aria-expanded={!isCollapsed}
                >
                    <span className="screen-reader-text">Toggle panel: {title}</span>
                    {isCollapsed ? <ChevronDown /> : <ChevronUp />}
                </button>
            </div>
            <div className="bottom-drawer-content">{!isCollapsed && (
                <>
                {!!error && (
                    <Notice status="error" isDismissible={false}>
                        {error}
                    </Notice>
                )}

                {loading && <Spinner />}

                {!loading && relations.length === 0 && (
                    <p className="ccwp-drawer-empty-message">
                        No {title.toLowerCase()} attached yet. Use the sidebar to attach.
                    </p>
                )}

                {/* Cast Relations */}
                {castRelations.length > 0 && (
                    <div className="ccwp-drawer-section">
                        <h3 className="ccwp-drawer-section-title">
                            Cast ({castRelations.length})
                        </h3>
                        {castRelations.map(r => {
                            const current = getCurrentRelation(r);
                            const key = getRelationKey(r);
                            const isSaving = savingId === key;

                            return (
                                <div
                                    key={key}
                                    className="ccwp-drawer-relation-row"
                                >
                                    <div className="ccwp-drawer-relation-id">
                                        {isProduction ? `#${r.cast_and_crew_id}` : `#${r.production_id}`}
                                    </div>
                                    <TextControl
                                        __next40pxDefaultSize
                                        __nextHasNoMarginBottom
                                        value={current.role || ''}
                                        onChange={(val: string) => handleFieldChange(r, 'role', val)}
                                        placeholder="Role"
                                    />
                                    <TextControl
                                        __next40pxDefaultSize
                                        __nextHasNoMarginBottom
                                        type="number"
                                        value={typeof current.custom_order === 'number' ? String(current.custom_order) : ''}
                                        onChange={(val: string) => handleFieldChange(r, 'custom_order', val)}
                                        placeholder="Order"
                                    />
                                    <Button
                                        variant="primary"
                                        onClick={() => handleSave(r)}
                                        disabled={!hasChanges(r) || isSaving}
                                        isBusy={isSaving}
                                        size="small"
                                    >
                                        Save
                                    </Button>
                                    <Button
                                        variant="secondary"
                                        isDestructive
                                        onClick={() => handleDetach(r)}
                                        disabled={isSaving}
                                        size="small"
                                    >
                                        Remove
                                    </Button>
                                </div>
                            );
                        })}
                    </div>
                )}

                {/* Crew Relations */}
                {crewRelations.length > 0 && (
                    <div className="ccwp-drawer-section">
                        <h3 className="ccwp-drawer-section-title">
                            Crew ({crewRelations.length})
                        </h3>
                        {crewRelations.map(r => {
                            const current = getCurrentRelation(r);
                            const key = getRelationKey(r);
                            const isSaving = savingId === key;

                            return (
                                <div
                                    key={key}
                                    className="ccwp-drawer-relation-row"
                                >
                                    <div className="ccwp-drawer-relation-id">
                                        {isProduction ? `#${r.cast_and_crew_id}` : `#${r.production_id}`}
                                    </div>
                                    <TextControl
                                        __next40pxDefaultSize
                                        __nextHasNoMarginBottom
                                        value={current.role || ''}
                                        onChange={(val: string) => handleFieldChange(r, 'role', val)}
                                        placeholder="Role"
                                    />
                                    <TextControl
                                        __next40pxDefaultSize
                                        __nextHasNoMarginBottom
                                        type="number"
                                        value={typeof current.custom_order === 'number' ? String(current.custom_order) : ''}
                                        onChange={(val: string) => handleFieldChange(r, 'custom_order', val)}
                                        placeholder="Order"
                                    />
                                    <Button
                                        variant="primary"
                                        onClick={() => handleSave(r)}
                                        disabled={!hasChanges(r) || isSaving}
                                        isBusy={isSaving}
                                        size="small"
                                    >
                                        Save
                                    </Button>
                                    <Button
                                        variant="secondary"
                                        isDestructive
                                        onClick={() => handleDetach(r)}
                                        disabled={isSaving}
                                        size="small"
                                    >
                                        Remove
                                    </Button>
                                </div>
                            );
                        })}
                    </div>
                )}
                </>
            )}
            </div>
        </div>
    );
};

const BottomDrawer: FC = () => {
    const [container, setContainer] = useState<HTMLElement | null>(null);
    const containerRef = useRef<HTMLDivElement | null>(null);

    useEffect(() => {
        // Find or create the drawer container
        let drawerContainer = document.getElementById('ccwp-bottom-drawer-container');

        if (!drawerContainer) {
            drawerContainer = document.createElement('div');
            drawerContainer.id = 'ccwp-bottom-drawer-container';
            drawerContainer.className = 'ccwp-bottom-drawer-wrapper';

            // Find the navigable region
            const navigableRegion = document.querySelector('.admin-ui-navigable-region.interface-interface-skeleton__content');

            if (navigableRegion) {
                // Find the metabox container
                const metaboxContainer = navigableRegion.querySelector('#postbox-container-0, .metabox-holder');

                if (metaboxContainer && metaboxContainer.nextSibling) {
                    // Insert after metabox container
                    navigableRegion.insertBefore(drawerContainer, metaboxContainer.nextSibling);
                } else if (metaboxContainer) {
                    // Append after metabox container
                    navigableRegion.appendChild(drawerContainer);
                } else {
                    // No metabox container found, just append to navigable region
                    navigableRegion.appendChild(drawerContainer);
                }
            } else {
                // Fallback: try to find alternate locations
                const fallbackRegion =
                    document.querySelector('.interface-interface-skeleton__content') ||
                    document.querySelector('.edit-post-layout__content') ||
                    document.body;
                fallbackRegion?.appendChild(drawerContainer);
            }
        }

        containerRef.current = drawerContainer as HTMLDivElement;
        setContainer(drawerContainer);
    }, []);

    if (!container) {
        return null;
    }

    return createPortal(<DrawerContent />, container);
};

export default BottomDrawer;
