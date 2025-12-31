import {Notice, Tooltip} from '@wordpress/components';
import {useDispatch, useSelect} from '@wordpress/data';
import {store as editorStore} from '@wordpress/editor';
import {__} from '@wordpress/i18n';
import {type FC, useEffect, useRef, useState} from 'react';
import CastCrewData from '../../data/CastCrewData';
import ProductionData from '../../data/ProductionData';
import PostType from '../../enums/PostType';
import ChevronDown from '../../icons/ChevronDown';
import ChevronUp from '../../icons/ChevronUp';
import {AttachData, DetachData, STORE_NAME} from '../../stores/relations-store';
import Relations from './Relations';

type DrawerContentState = {
    isCollapsed: boolean;
    isSaving: boolean;
    isRemoving: boolean;
    drawerHeight: number;
};

const DrawerContent: FC = () => {
    const [state, setState] = useState<DrawerContentState>({
        isCollapsed: false,
        isSaving: false,
        isRemoving: false,
        drawerHeight: 400,
    });
    const setIsCollapsed = (value: boolean) => setState(prevState => ({...prevState, isCollapsed: value}));
    const setIsSaving = (value: boolean) => setState(prevState => ({...prevState, isSaving: value}));
    const setIsRemoving = (value: boolean) => setState(prevState => ({...prevState, isRemoving: value}));
    const setDrawerHeight = (value: number) => setState(prevState => ({...prevState, drawerHeight: value}));

    const drawerRef = useRef<HTMLDivElement | null>(null);
    const resizeHandleRef = useRef<HTMLButtonElement | null>(null);
    const isResizingRef = useRef(false);
    const startYRef = useRef(0);
    const startHeightRef = useRef(0);

    const postId: string | number = useSelect(select => select(editorStore).getCurrentPostId(), []);
    const postType: PostType = useSelect(select => select(editorStore).getCurrentPostType(), []);
    const relations = useSelect(
        select => select(STORE_NAME).getRelations() as ((CastCrewData[]) | (ProductionData[])),
        []
    );
    const isFetching = useSelect(select => select(STORE_NAME).isLoading() as boolean, []);
    const fetchingError = useSelect(select => select(STORE_NAME).getError() as string | null, []);
    const {attachCastCrew, attachProduction, detach, fetchCastCrew, fetchProductions} = useDispatch(STORE_NAME);
    const title = postType === PostType.Production ? 'Cast & Crew' : 'Productions';

    const fetchRelations = async () => {
        if (!postId || !postType) {
            console.error('Can not fetch relations. Invalid postId or postType');
            return;
        }

        try {
            if (postType === PostType.CastCrew) {
                await fetchProductions(postId);
            } else if (postType === PostType.Production) {
                await fetchCastCrew(postId);
            }
        } catch (e) {
            console.error('Error fetching relations:', e);
        }
    };

    const handleSave = async (data: AttachData) => {
        if (!postId || !postType) {
            console.error('Can not save relation. Invalid postId or postType');
            return;
        }

        setIsSaving(true);

        try {
            if (postType === PostType.CastCrew) {
                await attachProduction(data);
            } else if (postType === PostType.Production) {
                await attachCastCrew(data);
            }
        } catch (e) {
            console.error('Error updating relation:', e);
        } finally {
            setIsSaving(false);
        }
    };

    const handleRemove = async (data: DetachData) => {
        if (!postId || !postType) {
            console.error('Can not remove relation. Invalid postId or postType');
            return;
        }

        if (confirm('Are you sure you want to remove this relation?')) {
            setIsRemoving(true);

            try {
                await detach(data);
            } catch (e) {
                console.error('Error detaching relation:', e);
            } finally {
                setIsRemoving(false);
            }
        }
    };

    // Handle resize
    useEffect(() => {
        const handleMouseDown = (e: MouseEvent) => {
            if (resizeHandleRef.current && !resizeHandleRef.current.contains(e.target as Node)) {
                return;
            }

            isResizingRef.current = true;
            startYRef.current = e.clientY;
            startHeightRef.current = state.drawerHeight;

            e.preventDefault();
            document.body.style.cursor = 'ns-resize';
            document.body.style.userSelect = 'none';
        };

        const handleMouseMove = (e: MouseEvent) => {
            if (!isResizingRef.current) {
                return;
            }

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
    }, [state.drawerHeight]);

    useEffect(() => {
        if (postId && postType) {
            fetchRelations().then();
        }
    // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [postId, postType]);

    return (
        <div
            ref={drawerRef}
            className={`ccwp-bottom-drawer${state.isCollapsed ? ' closed' : ''}`}
            style={{height: state.isCollapsed ? 'auto' : `${state.drawerHeight}px`}}
        >
            <div className="bottom-drawer-header">
                <Tooltip text={__('Drag to resize')}>
                    <button
                        className="bottom-drawer-separator"
                        ref={resizeHandleRef}
                        role="separator"
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
                    onClick={() => setIsCollapsed(!state.isCollapsed)}
                    aria-expanded={!state.isCollapsed}
                >
                    <span className="screen-reader-text">Toggle panel: {title}</span>
                    {state.isCollapsed ? <ChevronDown /> : <ChevronUp />}
                </button>
            </div>
            {state.isCollapsed ? (
                <div className="bottom-drawer-content" />
            ) : (
                <div className="bottom-drawer-content">
                    {!!fetchingError && (
                        <Notice status="error" isDismissible={false}>
                            {fetchingError}
                        </Notice>
                    )}
                    <Relations
                        isFetching={isFetching}
                        isRemoving={state.isRemoving}
                        isSaving={state.isSaving}
                        onRemove={handleRemove}
                        onSave={handleSave}
                        postId={postId}
                        postType={postType}
                        relations={relations}
                        title={title}
                    />
                </div>
            )}
        </div>
    );
};

DrawerContent.displayName = 'DrawerContent';

export default DrawerContent;
