import {Button, SelectControl, TextControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {FC, useMemo, useState} from 'react';
import ProductionData from '../../data/ProductionData';
import MemberType from '../../enums/MemberType';
import {AttachData, DetachData} from '../../stores/relations-store';
import {TEXT_DOMAIN} from '../../utils/constants';

export type ProductionRowState = {
    order: string;
    role: string;
    type: MemberType;
};

export type ProductionRowProps = {
    castcrewId: string | number;
    isRemoving?: boolean;
    isSaving?: boolean;
    onRemove?: (data: DetachData) => void;
    onSave?: (data: AttachData) => void;
    production: ProductionData;
};

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
                    {label: __('Cast', TEXT_DOMAIN), value: MemberType.Cast},
                    {label: __('Crew', TEXT_DOMAIN), value: MemberType.Crew},
                ]}
                onChange={type => setType(type as MemberType)}
            />
            <TextControl
                __next40pxDefaultSize
                __nextHasNoMarginBottom
                value={state.role}
                onChange={setRole}
                placeholder={__('Role', TEXT_DOMAIN)}
            />
            <TextControl
                __next40pxDefaultSize
                __nextHasNoMarginBottom
                type="number"
                value={state.order || '0'}
                onChange={setOrder}
                placeholder={__('Order', TEXT_DOMAIN)}
            />
            <Button
                __next40pxDefaultSize
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
                {__('Save', TEXT_DOMAIN)}
            </Button>
            <Button
                __next40pxDefaultSize
                variant="secondary"
                isDestructive
                onClick={() => onRemove?.({
                    productionId: production.id as string | number,
                    castcrewId,
                    type: state.type,
                })}
                disabled={isBusy}
                size="small"
            >
                {__('Remove', TEXT_DOMAIN)}
            </Button>
        </div>
    );
};

ProductionRow.displayName = 'ProductionRow';

export default ProductionRow;
