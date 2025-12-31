import {Button, SelectControl, TextControl} from '@wordpress/components';
import {FC, useMemo, useState} from 'react';
import ProductionData from '../../data/ProductionData';
import MemberType from '../../enums/MemberType';
import {AttachData, DetachData} from '../../stores/relations-store';

export type ProductionRowState = {
    order: string;
    role: string;
    type: MemberType;
}

export type ProductionRowProps = {
    castcrewId: string | number;
    isRemoving?: boolean;
    isSaving?: boolean;
    onRemove?: (data: DetachData) => void;
    onSave?: (data: AttachData) => void;
    production: ProductionData;
}

const ProductionRow: FC<ProductionRowProps> = ({
    castcrewId,
    isRemoving = false,
    isSaving = false,
    onRemove,
    onSave,
    production,
}) => {
    const [state, setState] = useState<ProductionRowState>({
        type: (production.memberType as MemberType) || MemberType.Cast,
        role: production.role || '',
        order: production.order ? production.order.toString() : '0',
    });
    const setType = (type: MemberType) => setState(current => ({...current, type}));
    const setRole = (role: string) => setState(current => ({...current, role}));
    const setOrder = (order: string) => setState(current => ({...current, order}));
    const isBusy = isSaving || isRemoving;
    const hasChanges = useMemo(
        () => (
            state.type !== (production.memberType as MemberType) ||
            state.role !== production.role ||
            parseInt(state.order, 10) !== (production.order || 0)
        ),
        [production, state.type, state.role, state.order]
    );

    return (
        <div className="ccwp-drawer-relation-row">
            <div className="ccwp-drawer-relation-id">
                {production.name}
            </div>
            <SelectControl<MemberType>
                __next40pxDefaultSize
                __nextHasNoMarginBottom
                value={state.type}
                options={[
                    {label: 'Cast', value: MemberType.Cast},
                    {label: 'Crew', value: MemberType.Crew},
                ]}
                onChange={setType}
            />
            <TextControl
                __next40pxDefaultSize
                __nextHasNoMarginBottom
                value={production.role || ''}
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
                    productionId: production.id as string | number,
                    castcrewId,
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
                    productionId: production.id as string | number,
                    castcrewId,
                })}
                disabled={isBusy}
                size="small"
            >
                Remove
            </Button>
        </div>
    )
}

ProductionRow.displayName = 'ProductionRow';

export default ProductionRow;
