import {Button, TextControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {FC} from 'react';
import MemberType from '../../enums/MemberType';
import {ProductionCastCrew} from '../../types/metaboxes';
import {TEXT_DOMAIN} from '../../utils/constants';

type UpdateData = {
    role?: string;
    order?: number;
};

export type RowProps = {
    productionId: string | number | null;
    member: ProductionCastCrew;
    onUpdate?: (type: MemberType, id: number | string, data: UpdateData) => void;
    onRemove?: (type: MemberType, id: number | string) => void;
};

const Row: FC<RowProps> = ({
    productionId,
    member,
    onUpdate,
    onRemove,
}) => {
    const inputName = `ccwp_add_${member.type}_to_production`;

    return (
        <div className="form-group ccwp-production-castcrew-form-group">
            <input
                type="hidden"
                name={`${inputName}[${member.ID}][cast_and_crew_id]`}
                value={member.ID}
            />
            <input
                type="hidden"
                name={`${inputName}[${member.ID}][production_id]`}
                value={productionId ?? ''}
            />
            <input
                type="hidden"
                name={`${inputName}[${member.ID}][type]`}
                value={member.type}
            />
            <div
                className="ccwp-row"
                style={{
                    maxWidth: '100%',
                    width: '100%',
                    display: 'grid',
                    gridAutoFlow: 'column',
                    alignItems: 'center',
                    gridTemplateColumns: '2fr 2fr 1fr 1fr',
                    gap: '12px',
                    marginBottom: '10px',
                }}
            >
                <div className="ccwp-col name-col" style={{flex: '1'}}>
                    <div className="ccwp-castcrew-name">
                        <strong>{member.fullName}</strong>
                    </div>
                </div>
                <div className="ccwp-col role-col" style={{flex: '1'}}>
                    <TextControl
                        __next40pxDefaultSize
                        __nextHasNoMarginBottom
                        value={member.role || ''}
                        onChange={val => onUpdate?.(member.type, member.ID, {role: val})}
                        name={`${inputName}[${member.ID}][role]`}
                        placeholder={__('role', TEXT_DOMAIN)}
                    />
                </div>
                <div className="ccwp-col billing-col" style={{flex: '0 0 100px'}}>
                    <TextControl
                        __next40pxDefaultSize
                        __nextHasNoMarginBottom
                        type="number"
                        value={member.order || 0}
                        onChange={val => onUpdate?.(member.type, member.ID, {order: parseInt(val, 10)})}
                        name={`${inputName}[${member.ID}][custom_order]`}
                        placeholder={__('order', TEXT_DOMAIN)}
                    />
                </div>
                <div className="ccwp-col action-col">
                    <Button
                        __next40pxDefaultSize
                        isDestructive
                        onClick={() => onRemove?.(member.type, member.ID)}
                    >
                        {__('Delete', TEXT_DOMAIN)}
                    </Button>
                </div>
            </div>
        </div>
    );
};

Row.displayName = 'ProductionCastCrewMetaboxRow';

export default Row;
