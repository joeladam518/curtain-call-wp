import {FC, useMemo, useState} from 'react';
import {Button, TextControl} from '@wordpress/components';
import CastCrewData from '../../data/CastCrewData';
import MemberType from '../../enums/MemberType';
import {AttachData, DetachData} from '../../stores/relations-store';

export type ProductionRowState = {
    type: MemberType;
    role: string;
    order: string;
};

export type ProductionRowProps = {
    castcrew: CastCrewData;
    isRemoving?: boolean;
    isSaving?: boolean;
    onRemove?: (data: DetachData) => void;
    onSave?: (data: AttachData) => void;
    productionId: string | number;
};

const CastCrewRow: FC<ProductionRowProps> = ({
    castcrew,
    isRemoving = false,
    isSaving = false,
    onRemove,
    onSave,
    productionId,
}) => {
    const [state, setState] = useState<ProductionRowState>({
        type: (castcrew.memberType as MemberType) || MemberType.Cast,
        role: castcrew.role || '',
        order: castcrew.order ? castcrew.order.toString() : '0',
    });
    const setRole = (role: string) => {
        console.log(`setting role for production ${productionId} to castcrew ${castcrew.id} type ${state.type} === ${role}`)
        setState(current => ({...current, role}));
    }
    const setOrder = (order: string) => setState(current => ({...current, order}));
    const isBusy = isSaving || isRemoving;
    const hasChanges = useMemo(
        () => (
            state.type !== (castcrew.memberType as MemberType) ||
            state.role !== castcrew.role ||
            parseInt(state.order, 10) !== (castcrew.order || 0)
        ),
        [castcrew, state.type, state.role, state.order]
    );

    return (
        <div className="ccwp-drawer-relation-row">
            <div className="ccwp-drawer-relation-id">
                {castcrew.fullName || `${castcrew.firstName || ''} ${castcrew.lastName || ''}`.trim()}
            </div>
            <TextControl
                __next40pxDefaultSize
                __nextHasNoMarginBottom
                value={state.role}
                onChange={setRole}
                placeholder="Role"
            />
            <TextControl
                __next40pxDefaultSize
                __nextHasNoMarginBottom
                type="number"
                value={state.order || '0'}
                onChange={setOrder}
                placeholder="Order"
            />
            <Button
                variant="primary"
                onClick={() => onSave?.({
                    productionId,
                    castcrewId: castcrew.id as string | number,
                    type: state.type,
                    role: state.role,
                    customOrder: parseInt(state.order, 10) || 0,
                })}
                disabled={!hasChanges || isSaving}
                isBusy={isBusy}
                size="small"
            >
                Save
            </Button>
            <Button
                variant="secondary"
                isDestructive
                onClick={() => onRemove?.({
                    productionId,
                    castcrewId: castcrew.id as string | number,
                    type: state.type,
                })}
                disabled={isBusy}
                size="small"
            >
                Remove
            </Button>
        </div>
    );
};

CastCrewRow.displayName = 'ProductionRow';

export default CastCrewRow;
