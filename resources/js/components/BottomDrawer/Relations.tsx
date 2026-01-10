import {Spinner} from '@wordpress/components';
import {__, sprintf} from '@wordpress/i18n';
import {FC} from 'react';
import CastCrewData from '../../data/CastCrewData';
import ProductionData from '../../data/ProductionData';
import PostType from '../../enums/PostType';
import {AttachData, DetachData} from '../../stores/relations-store';
import {TEXT_DOMAIN} from '../../utils/constants';
import CastCrewRelations from './CastCrewRelations';
import ProductionRelations from './ProductionRelations';

type RelationsProps = {
    isFetching?: boolean;
    isRemoving?: boolean;
    isSaving?: boolean;
    onRemove?: (data: DetachData) => void;
    onSave?: (data: AttachData) => void;
    postId: string | number | null;
    postType: PostType | null;
    relations: ((CastCrewData[]) | (ProductionData[]));
    title?: string;
};

const Relations: FC<RelationsProps> = ({
    isFetching = false,
    isRemoving = false,
    isSaving = false,
    onRemove,
    onSave,
    postId,
    postType,
    relations,
    title,
}) => {
    if (isFetching) {
        return (
            <Spinner />
        );
    }

    if (!relations || relations.length === 0) {
        return (
            <p className="ccwp-drawer-empty-message">
                {sprintf(
                    /* translators: %s: the type of relation (e.g., "cast", "crew", "productions") */
                    __('No %s attached yet. Use the sidebar to attach.', TEXT_DOMAIN),
                    (title || __('relations', TEXT_DOMAIN)).toLowerCase()
                )}
            </p>
        );
    }

    if (!postId) {
        return (
            <p className="ccwp-drawer-empty-message">
                {__('Invalid Post Id', TEXT_DOMAIN)}
            </p>
        );
    }

    if (postType === PostType.CastCrew) {
        return (
            <CastCrewRelations
                isSaving={isSaving}
                isRemoving={isRemoving}
                castcrewId={postId}
                productions={relations as ProductionData[]}
                onSave={onSave}
                onRemove={onRemove}
            />
        );
    }

    if (postType === PostType.Production) {
        return (
            <ProductionRelations
                isSaving={isSaving}
                isRemoving={isRemoving}
                productionId={postId}
                members={relations as CastCrewData[]}
                onSave={onSave}
                onRemove={onRemove}
            />
        );
    }

    return (
        <p className="ccwp-drawer-empty-message">
            {__('Invalid Post Type', TEXT_DOMAIN)}
        </p>
    );
};

Relations.displayName = 'Relations';

export default Relations;
