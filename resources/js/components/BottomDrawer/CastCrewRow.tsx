import {FC, useMemo, useState} from 'react';
import {Button, TextControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import CastCrewData from '../../data/CastCrewData';
import MemberType from '../../enums/MemberType';
import {AttachData, DetachData} from '../../stores/relations-store';
import {TEXT_DOMAIN} from '../../utils/constants';

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
        order: castcrew.order ? castcrew.order.toString() : '',
    });
    const setRole = (role: string) => {
        setState(current => ({...current, role}));
    };
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
                placeholder={__('Role', TEXT_DOMAIN)}
            />
            <TextControl
                __next40pxDefaultSize
                __nextHasNoMarginBottom
                type="number"
                value={state.order}
                onChange={setOrder}
                placeholder={__('Order', TEXT_DOMAIN)}
            />
            <Button
                __next40pxDefaultSize
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
                {__('Save', TEXT_DOMAIN)}
            </Button>
            <Button
                __next40pxDefaultSize
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
                {__('Remove', TEXT_DOMAIN)}
            </Button>
        </div>
    );
};

CastCrewRow.displayName = 'ProductionRow';

export default CastCrewRow;
