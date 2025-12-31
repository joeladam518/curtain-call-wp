import React, {FC} from 'react';
import ProductionData from '../../data/ProductionData';
import {AttachData, DetachData} from '../../stores/relations-store';
import ProductionRow from './ProductionRow';

type CastCrewRelationsProps = {
    castcrewId: string | number;
    isRemoving?: boolean;
    isSaving?: boolean;
    onRemove?: (data: DetachData) => void;
    onSave?: (data: AttachData) => void;
    productions: ProductionData[];
};

const CastCrewRelations: FC<CastCrewRelationsProps> = ({
    castcrewId,
    isRemoving = false,
    isSaving = false,
    onRemove,
    onSave,
    productions,
}) => {
    return (
        <>
            {productions.map((production, index) => (
                <ProductionRow
                    key={`castcrew-production-${castcrewId}-${production.id}-${index}`}
                    castcrewId={castcrewId}
                    isRemoving={isRemoving}
                    isSaving={isSaving}
                    onRemove={onRemove}
                    onSave={onSave}
                    production={production}
                />
            ))}
        </>
    );
};

CastCrewRelations.displayName = 'CastCrewRelations';

export default CastCrewRelations;
