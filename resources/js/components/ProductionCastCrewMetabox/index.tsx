import {FC, useState} from 'react';
import {Button, ComboboxControl} from '@wordpress/components';
import MemberType from '../../enums/MemberType';
import {ProductionCastCrew} from '../../types/metaboxes';
import Row from './Row';

type UpdateData = {
    role?: string;
    order?: number;
};

export type MetaboxState = {
    cast: ProductionCastCrew<MemberType.Cast>[];
    crew: ProductionCastCrew<MemberType.Crew>[];
    selectedCastId: string;
    selectedCrewId: string;
};

export type ProductionCastCrewMetaboxProps = {
    productionId: number | string | null;
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
    const addCast = (cast: ProductionCastCrew<MemberType.Cast>) => setMetaboxState((current) => {
        const castMembers = [...current.cast];
        castMembers.push(cast);
        return {...current, cast: castMembers};
    });
    const setCrew = (crew: ProductionCastCrew<MemberType.Crew>[]) => setMetaboxState(
        current => ({...current, crew})
    );
    const addCrew = (crew: ProductionCastCrew<MemberType.Crew>) => setMetaboxState((current) => {
        const crewMembers = [...current.crew];
        crewMembers.push(crew);
        return {...current, crew: crewMembers};
    });
    const setSelectedCastId = (id: string) => setMetaboxState(current => ({...current, selectedCastId: id}));
    const setSelectedCrewId = (id: string) => setMetaboxState(current => ({...current, selectedCrewId: id}));

    const addMember = (type: MemberType) => {
        const idVal = type === MemberType.Cast ? state.selectedCastId : state.selectedCrewId;
        const id = parseInt(idVal, 10);

        if (!id) {
            return;
        }

        const option = options.find(member => member.value === idVal);

        if (!option) {
            return;
        }

        const newMember: ProductionCastCrew = {
            ID: id,
            firstName: '',
            lastName: '',
            fullName: option.label,
            order: 0,
            role: '',
            type,
        };

        if (type === MemberType.Cast) {
            addCast(newMember as ProductionCastCrew<MemberType.Cast>);
        } else if (type === MemberType.Crew) {
            addCrew(newMember as ProductionCastCrew<MemberType.Crew>);
        }
    };

    const removeMember = (type: MemberType, id: number | string) => {
        if (type === MemberType.Cast) {
            setCast(state.cast.filter(m => m.ID !== id));
        } else if (type === MemberType.Crew) {
            setCrew(state.crew.filter(m => m.ID !== id));
        }
    };

    const updateMember = (type: MemberType, id: number | string, data: UpdateData) => {
        const updateFn = (members: ProductionCastCrew[]) => members.map(m => m.ID === id ? {...m, ...data} : m);
        if (type === MemberType.Cast) {
            const newCast = updateFn(state.cast) as ProductionCastCrew<MemberType.Cast>[];
            setCast(newCast);
        } else if (type === MemberType.Crew) {
            const newCrew = updateFn(state.crew) as ProductionCastCrew<MemberType.Crew>[];
            setCrew(newCrew);
        }
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
                        __next40pxDefaultSize
                        __nextHasNoMarginBottom
                        label="Add Cast"
                        value={state.selectedCastId}
                        options={[{label: 'Select Cast', value: '0'}, ...options]}
                        onChange={value => setSelectedCastId(value || '0')}
                    />
                </div>
                <Button
                    variant="secondary"
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
                {state.cast.map((member: ProductionCastCrew, index: number) => (
                    <Row
                        key={`add_cast_row_${member.ID}_${index}`}
                        productionId={productionId}
                        member={member}
                        onUpdate={updateMember}
                        onRemove={removeMember}
                    />
                ))}
            </div>

            {/* Crew Section */}
            <div
                className="ccwp-production-castcrew-select-wrap"
                style={{marginTop: '25px'}}
            >
                <ComboboxControl
                    __next40pxDefaultSize
                    __nextHasNoMarginBottom
                    label="Add Crew"
                    value={state.selectedCrewId}
                    options={[{label: 'Select Crew', value: '0'}, ...options]}
                    onChange={value => setSelectedCrewId(value || '0')}
                />
                <Button
                    variant="secondary"
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
                {state.crew.map((member: ProductionCastCrew, index: number) => (
                    <Row
                        key={`add_crew_row_${member.ID}_${index}`}
                        productionId={productionId}
                        member={member}
                        onUpdate={updateMember}
                        onRemove={removeMember}
                    />
                ))}
            </div>
        </div>
    );
};

ProductionCastCrewMetabox.displayName = 'ProductionCastCrewMetabox';

export default ProductionCastCrewMetabox;
