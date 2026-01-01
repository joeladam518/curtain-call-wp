import {FC, useState} from 'react';
import {Button, ComboboxControl, SelectControl, TextControl} from '@wordpress/components';
import MemberType from '../../enums/MemberType';
import {CastCrewProduction} from '../../types/metaboxes';
import Row from './Row';

type UpdateData = {
    role?: string;
    order?: number;
};

export type MetaboxState = {
    cast: CastCrewProduction[];
    crew: CastCrewProduction[];
    selectedProductionId: string;
    selectedType: MemberType;
    role: string;
    order: number;
};

export type CastCrewProductionsMetaboxProps = {
    castCrewId: number | null;
    options: ({label: string; value: string})[];
    productions: CastCrewProduction[];
};

const CastCrewProductionsMetabox: FC<CastCrewProductionsMetaboxProps> = ({
    castCrewId,
    options = [],
    productions: initialProductions,
}) => {
    const [state, setMetaboxState] = useState<MetaboxState>({
        cast: initialProductions?.filter(p => p.type === MemberType.Cast) as CastCrewProduction[] ?? [],
        crew: initialProductions?.filter(p => p.type === MemberType.Crew) as CastCrewProduction[] ?? [],
        selectedProductionId: '0',
        selectedType: MemberType.Cast,
        role: '',
        order: 0,
    });

    const setCast = (cast: CastCrewProduction<MemberType.Cast>[]) => setMetaboxState(
        current => ({...current, cast})
    );
    const addCast = (cast: CastCrewProduction<MemberType.Cast>) => setMetaboxState((current) => {
        const castProductions = [...current.cast];
        castProductions.push(cast);
        return {...current, cast: castProductions};
    });
    const setCrew = (crew: CastCrewProduction<MemberType.Crew>[]) => setMetaboxState(
        current => ({...current, crew})
    );
    const addCrew = (crew: CastCrewProduction<MemberType.Crew>) => setMetaboxState((current) => {
        const crewProductions = [...current.crew];
        crewProductions.push(crew);
        return {...current, crew: crewProductions};
    });
    const setSelectedProductionId = (id: string) => setMetaboxState(current => ({
        ...current,
        selectedProductionId: id,
    }));
    const setSelectedType = (type: MemberType) => setMetaboxState(current => ({...current, selectedType: type}));
    const setRole = (role: string) => setMetaboxState(current => ({...current, role}));
    const setOrder = (order: number) => setMetaboxState(current => ({...current, order}));

    const addProduction = () => {
        const id = parseInt(state.selectedProductionId, 10);

        if (!id) {
            return;
        }

        const option = options.find(production => production.value === state.selectedProductionId);

        if (!option) {
            return;
        }

        const newProduction: CastCrewProduction = {
            ID: id,
            dateEnd: null,
            dateStart: null,
            name: option.label,
            order: state.order,
            role: state.role || null,
            type: state.selectedType,
        };

        if (state.selectedType === MemberType.Cast) {
            addCast(newProduction as CastCrewProduction<MemberType.Cast>);
        } else if (state.selectedType === MemberType.Crew) {
            addCrew(newProduction as CastCrewProduction<MemberType.Crew>);
        }

        // Reset form fields
        setMetaboxState(current => ({
            ...current,
            selectedProductionId: '0',
            role: '',
            order: 0,
        }));
    };

    const removeProduction = (type: MemberType, id: number | string) => {
        if (type === MemberType.Cast) {
            setCast(state.cast.filter(p => p.ID !== id) as CastCrewProduction<MemberType.Cast>[]);
        } else if (type === MemberType.Crew) {
            setCrew(state.crew.filter(p => p.ID !== id) as CastCrewProduction<MemberType.Crew>[]);
        }
    };

    const updateProduction = (type: MemberType, id: number | string, data: UpdateData) => {
        const updateFn = (productions: CastCrewProduction[]) => productions.map(p => p.ID === id ? {...p, ...data} : p);
        if (type === MemberType.Cast) {
            const newCast = updateFn(state.cast) as CastCrewProduction<MemberType.Cast>[];
            setCast(newCast);
        } else if (type === MemberType.Crew) {
            const newCrew = updateFn(state.crew) as CastCrewProduction<MemberType.Crew>[];
            setCrew(newCrew);
        }
    };

    return (
        <div className="ccwp-react-metabox">
            {/* Add Production Form */}
            <div
                className="ccwp-production-castcrew-select-wrap"
                style={{
                    display: 'grid',
                    gridTemplateColumns: '2fr 1fr 2fr 1fr auto',
                    gap: '10px',
                    alignItems: 'end',
                    marginBottom: '20px',
                }}
            >
                <ComboboxControl
                    __next40pxDefaultSize
                    __nextHasNoMarginBottom
                    label="Production"
                    value={state.selectedProductionId}
                    options={[{label: 'Select Production', value: '0'}, ...options]}
                    onChange={value => setSelectedProductionId(value || '0')}
                />
                <SelectControl<MemberType>
                    __next40pxDefaultSize
                    __nextHasNoMarginBottom
                    label="Type"
                    value={state.selectedType as NoInfer<string> | undefined}
                    options={[
                        {label: 'Cast', value: MemberType.Cast},
                        {label: 'Crew', value: MemberType.Crew},
                    ]}
                    onChange={value => setSelectedType(value as MemberType)}
                />
                <TextControl
                    __next40pxDefaultSize
                    __nextHasNoMarginBottom
                    label="Role"
                    value={state.role}
                    onChange={value => setRole(value)}
                    placeholder="e.g. Hamlet, Director"
                />
                <TextControl
                    __next40pxDefaultSize
                    __nextHasNoMarginBottom
                    label="Order"
                    type="number"
                    value={state.order}
                    onChange={value => setOrder(parseInt(value, 10) || 0)}
                    placeholder="0"
                />
                <Button
                    variant="secondary"
                    onClick={() => addProduction()}
                    style={{marginBottom: '8px'}}
                >
                    Add Production
                </Button>
            </div>

            {/* Cast Productions List */}
            {state.cast.length > 0 && (
                <>
                    <h4 style={{marginTop: '25px', marginBottom: '10px'}}>Cast Productions</h4>
                    <div id="ccwp-cast-crew-cast-wrap">
                        <div className="ccwp-row label-row">
                            <div className="ccwp-col name-col">Name</div>
                            <div className="ccwp-col role-col">Role</div>
                            <div className="ccwp-col billing-col">Billing</div>
                            <div className="ccwp-col action-col">&nbsp;</div>
                        </div>
                        {state.cast.map((production: CastCrewProduction, index: number) => (
                            <Row
                                key={`add_cast_row_${production.ID}_${index}`}
                                castCrewId={castCrewId}
                                production={production}
                                onUpdate={updateProduction}
                                onRemove={removeProduction}
                            />
                        ))}
                    </div>
                </>
            )}

            {/* Crew Productions List */}
            {state.crew.length > 0 && (
                <>
                    <h4 style={{marginTop: '25px', marginBottom: '10px'}}>Crew Productions</h4>
                    <div id="ccwp-cast-crew-crew-wrap">
                        <div className="ccwp-row label-row">
                            <div className="ccwp-col name-col">Name</div>
                            <div className="ccwp-col role-col">Role</div>
                            <div className="ccwp-col billing-col">Billing</div>
                            <div className="ccwp-col action-col">&nbsp;</div>
                        </div>
                        {state.crew.map((production: CastCrewProduction, index: number) => (
                            <Row
                                key={`add_crew_row_${production.ID}_${index}`}
                                castCrewId={castCrewId}
                                production={production}
                                onUpdate={updateProduction}
                                onRemove={removeProduction}
                            />
                        ))}
                    </div>
                </>
            )}
        </div>
    );
};

CastCrewProductionsMetabox.displayName = 'CastCrewProductionsMetabox';

export default CastCrewProductionsMetabox;
