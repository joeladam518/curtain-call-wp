import {Spinner} from '@wordpress/components';
import {FC} from 'react';
import CastCrewData from '../../data/CastCrewData';
import ProductionData from '../../data/ProductionData';
import PostType from '../../enums/PostType';
import {AttachData, DetachData} from '../../stores/relations-store';
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
                No {(title || 'relations').toLowerCase()} attached yet. Use the sidebar to attach.
            </p>
        );
    }

    if (!postId) {
        return (
            <p className="ccwp-drawer-empty-message">
                Invalid Post Id
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
            Invalid Post Type
        </p>
    );
};

Relations.displayName = 'Relations';

export default Relations;
