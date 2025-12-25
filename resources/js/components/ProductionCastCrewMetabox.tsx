import {Button, ComboboxControl, TextControl} from '@wordpress/components';
import React, {FC, useState} from 'react';
import MemberType from '../enums/MemberType';
import {ProductionCastCrew} from '../types/metaboxes';

type UpdateData = {
    role?: string;
    custom_order?: number;
};

export type MetaboxState = {
    cast: ProductionCastCrew<MemberType.Cast>[];
    crew: ProductionCastCrew<MemberType.Crew>[];
    selectedCastId: string;
    selectedCrewId: string;
};

export type ProductionCastCrewMetaboxProps = {
    productionId: number | null;
    options: ({label: string; value: string})[];
    cast: ProductionCastCrew<MemberType.Cast>[];
    crew: ProductionCastCrew<MemberType.Crew>[];
};

const ProductionCastCrewMetabox: FC<ProductionCastCrewMetaboxProps> = ({
    productionId,
    options = [],
    cast: initialCast,
    crew: initialCrew,
}) => {
    const [state, setMetaboxState] = useState<MetaboxState>({
        cast: initialCast ?? [],
        crew: initialCrew ?? [],
        selectedCastId: '0',
        selectedCrewId: '0',
    });

    const setCast = (cast: ProductionCastCrew<MemberType.Cast>[]) => setMetaboxState(
        current => ({...current, cast})
    );
    const addCast = (cast: ProductionCastCrew<MemberType.Cast>) => setMetaboxState(
        current => ({...current, cast: [...current.cast, cast]})
    );
    const setCrew = (crew: ProductionCastCrew<MemberType.Crew>[]) => setMetaboxState(
        current => ({...current, crew})
    );
    const addCrew = (crew: ProductionCastCrew<MemberType.Crew>) => setMetaboxState(
        current => ({...current, crew: [...current.crew, crew]})
    );
    const setSelectedCastId = (id: string) => setMetaboxState(current => ({...current, selectedCastId: id}));
    const setSelectedCrewId = (id: string) => setMetaboxState(current => ({...current, selectedCrewId: id}));

    const addMember = <Type extends MemberType>(type: Type) => {
        const idVal = type === MemberType.Cast ? state.selectedCastId : state.selectedCrewId;

        const id = parseInt(idVal, 10);
        if (!id) {
            return;
        }

        const option = options.find(m => parseInt(m.value, 10) === id);
        if (!option) {
            return;
        }

        const newMember: ProductionCastCrew<Type> = {
            ID: id,
            firstName: '',
            lastName: '',
            name: option.label,
            order: 0,
            role: '',
            type,
        };

        if (type === MemberType.Cast) {
            if (!state.cast.some(m => m.ID === id)) {
                addCast(newMember);
            }
        } else if (type === MemberType.Crew) {
            if (!state.crew.some(m => m.ID === id)) {
                addCrew(newMember);
            }
        }
    };

    const removeMember = (type: MemberType, id: number) => {
        if (type === MemberType.Cast) {
            setCast(state.cast.filter(m => m.ID !== id));
        } else if (type === MemberType.Crew) {
            setCrew(state.crew.filter(m => m.ID !== id));
        }
    };

    const updateMember = <Type extends MemberType>(type: Type, id: number, data: UpdateData) => {
        const updateFn = (members: ProductionCastCrew<Type>[]) => members.map(m => m.ID === id ? {...m, ...data} : m);
        if (type === MemberType.Cast) {
            setCast(updateFn(state.cast));
        } else if (type === MemberType.Crew) {
            setCrew(updateFn(state.crew));
        }
    };

    const renderMemberRow = <Type extends MemberType>(type: Type, member: ProductionCastCrew<Type>) => {
        const inputName = `ccwp_add_${type}_to_production`;
        return (
            <div key={`${type}-${member.ID}`} className="form-group ccwp-production-castcrew-form-group">
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
                    value={type}
                />
                <div
                    className="ccwp-row"
                    style={{display: 'flex', alignItems: 'center', gap: '10px', marginBottom: '10px'}}
                >
                    <div className="ccwp-col name-col" style={{flex: '1'}}>
                        <div className="ccwp-castcrew-name"><strong>{member.name}</strong></div>
                    </div>
                    <div className="ccwp-col role-col" style={{flex: '1'}}>
                        <TextControl
                            value={member.role}
                            onChange={val => updateMember(type, member.ID, {role: val})}
                            name={`${inputName}[${member.ID}][role]`}
                            placeholder="role"
                        />
                    </div>
                    <div className="ccwp-col billing-col" style={{flex: '0 0 100px'}}>
                        <TextControl
                            value={member.order}
                            onChange={val => updateMember(type, member.ID, {custom_order: parseInt(val, 10) || 0})}
                            name={`${inputName}[${member.ID}][custom_order]`}
                            placeholder="order"
                        />
                    </div>
                    <div className="ccwp-col action-col">
                        <Button isDestructive onClick={() => removeMember(type, member.ID)}>Delete</Button>
                    </div>
                </div>
            </div>
        );
    };

    return (
        <div className="ccwp-react-metabox">
            {/* Cast Section */}
            <div
                className="ccwp-production-castcrew-select-wrap"
                style={{display: 'flex', alignItems: 'flex-end', gap: '10px', marginBottom: '20px'}}
            >
                <div style={{flex: '1'}}>
                    <ComboboxControl
                        label="Add Cast"
                        value={state.selectedCastId}
                        options={[{label: 'Select Cast', value: '0'}, ...options]}
                        onChange={value => setSelectedCastId(value || '0')}
                    />
                </div>
                <Button
                    isSecondary
                    onClick={() => addMember(MemberType.Cast)}
                    style={{marginBottom: '8px'}}
                >
                    Add Cast
                </Button>
            </div>
            <div id="ccwp-production-cast-wrap">
                {state.cast.length > 0 && (
                    <div className="ccwp-row label-row">
                        <div className="ccwp-col name-col">Name</div>
                        <div className="ccwp-col role-col">Role</div>
                        <div className="ccwp-col billing-col">Billing</div>
                        <div className="ccwp-col action-col">&nbsp;</div>
                    </div>
                )}
                {state.cast.map(m => renderMemberRow(MemberType.Cast, m))}
            </div>

            {/* Crew Section */}
            <div
                className="ccwp-production-castcrew-select-wrap"
                style={{marginTop: '25px'}}
            >
                <ComboboxControl
                    label="Add Crew"
                    value={state.selectedCrewId}
                    options={[{label: 'Select Crew', value: '0'}, ...options]}
                    onChange={value => setSelectedCrewId(value || '0')}
                />
                <Button
                    isSecondary
                    onClick={() => addMember(MemberType.Crew)}
                >
                    Add Crew
                </Button>
            </div>
            <div id="ccwp-production-crew-wrap">
                {state.crew.length > 0 && (
                    <div className="ccwp-row label-row">
                        <div className="ccwp-col name-col">Name</div>
                        <div className="ccwp-col role-col">Role</div>
                        <div className="ccwp-col billing-col">Billing</div>
                        <div className="ccwp-col action-col">&nbsp;</div>
                    </div>
                )}
                {state.crew.map(m => renderMemberRow(MemberType.Crew, m))}
            </div>
        </div>
    );
};

ProductionCastCrewMetabox.displayName = 'ProductionCastCrewMetabox';

export default ProductionCastCrewMetabox;
