import MemberType from '../enums/MemberType';

export type ProductionDetails = {
    ID: number,
    name: string,
    date_start: string,
    date_end: string,
    show_times: string,
    ticket_url: string,
    venue: string,
}

export type CastCrewDetails = {
    ID: number,
    name_first: string;
    name_last: string;
    self_title: string;
    birthday: string;
    hometown: string;
    website_link: string;
    facebook_link: string;
    twitter_link: string;
    instagram_link: string;
    fun_fact: string;
}

export type ProductionCastCrew<Type extends MemberType = MemberType> = {
    ID: number,
    firstName: string,
    lastName: string,
    name: string,
    order: number,
    role: string,
    type: Type | null,
}

export type CastCrewProduction<Type extends MemberType = MemberType> = {
    ID: number,
    dateEnd: string | null,
    dateStart: string | null,
    name: string,
    order: number,
    role: string | null,
    type: Type,
}
