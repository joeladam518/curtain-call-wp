import {type Post} from '@wordpress/core-data';
import type CurtainCallPivot from './pivot';

export interface CastCrew extends Post {
    ccwp_join?: CurtainCallPivot | null | undefined,
}

export interface Production extends Post {
    ccwp_join?: CurtainCallPivot | null | undefined,
}
