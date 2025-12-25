import MemberType from '../enums/MemberType';

type CurtainCallPivot = {
    production_id: number,
    cast_and_crew_id: number,
    type: MemberType | null,
    role: string | null,
    custom_order: number | null,
}

export default CurtainCallPivot;
