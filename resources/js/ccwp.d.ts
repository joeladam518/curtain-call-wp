import MemberType from './enums/MemberType';
import {CastCrewDetails, CastCrewProduction, ProductionCastCrew, ProductionDetails} from './types/metaboxes';

declare global {
    interface Window {
        CCWP_DATA?: {
            initialDetails?: ProductionDetails | CastCrewDetails | null | undefined;
            options?: ({label: string; value: string})[];
            cast?: ProductionCastCrew<MemberType.Cast>[];
            crew?: ProductionCastCrew<MemberType.Crew>[];
            productions?: CastCrewProduction[];
        } | null | undefined;
        CCWP_SETTINGS?: {
            nonce: string;
        } | null | undefined;
    }
}
