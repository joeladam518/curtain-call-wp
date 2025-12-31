import {FC, useMemo} from 'react';
import CastCrewData from '../../data/CastCrewData';
import MemberType from '../../enums/MemberType';
import {AttachData, DetachData} from '../../stores/relations-store';
import CastCrewRow from './CastCrewRow';

type ProductionRelationsProps = {
    isRemoving?: boolean;
    isSaving?: boolean;
    members: CastCrewData[];
    onRemove?: (data: DetachData) => void;
    onSave?: (data: AttachData) => void;
    productionId: string | number;
};

const ProductionRelations: FC<ProductionRelationsProps> = ({
    isRemoving = false,
    isSaving = false,
    members,
    onRemove,
    onSave,
    productionId,
}) => {
    const castMembers = useMemo(() => members.filter(member => member.memberType === MemberType.Cast), [members]);
    const crewMembers = useMemo(() => members.filter(member => member.memberType === MemberType.Crew), [members]);

    return (
        <>
            {castMembers.length > 0 && (
                <div className="ccwp-drawer-section">
                    <h3 className="ccwp-drawer-section-title">
                        Cast ({castMembers.length})
                    </h3>
                    {castMembers.map((cast, index) => (
                        <CastCrewRow
                            key={`production-cast-${productionId}-${cast.id}-${index}`}
                            castcrew={cast}
                            isRemoving={isRemoving}
                            isSaving={isSaving}
                            onRemove={onRemove}
                            onSave={onSave}
                            productionId={productionId}
                        />
                    ))}
                </div>
            )}

            {crewMembers.length > 0 && (
                <div className="ccwp-drawer-section">
                    <h3 className="ccwp-drawer-section-title">
                        Crew ({crewMembers.length})
                    </h3>
                    {crewMembers.map((crew, index) => (
                        <CastCrewRow
                            key={`production-crew-${productionId}-${crew.id}-${index}`}
                            castcrew={crew}
                            isRemoving={isRemoving}
                            isSaving={isSaving}
                            onRemove={onRemove}
                            onSave={onSave}
                            productionId={productionId}
                        />
                    ))}
                </div>
            )}
        </>
    );
};

ProductionRelations.displayName = 'ProductionRelations';

export default ProductionRelations;
