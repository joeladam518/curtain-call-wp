import {CastCrewEntity} from '../types/cast-and-crew';

export type CastCrewDataProps = {
    birthday?: string | null;
    facebookLink?: string | null;
    firstName?: string | null;
    fullName?: string | null;
    funFact?: string | null;
    hometown?: string | null;
    id?: string | number | null;
    instagramLink?: string | null;
    lastName?: string | null;
    memberType?: string | null;
    order?: string | number | null
    role?: string | null;
    title?: string | null;
    twitterLink?: string | null;
    websiteLink?: string | null;
}

export default class CastCrewData {
    public birthday: string | null;
    public facebookLink: string | null;
    public firstName: string | null;
    public fullName: string | null;
    public funFact: string | null;
    public hometown: string | null;
    public id: string | number | null;
    public instagramLink: string | null;
    public lastName: string | null;
    public memberType: string | null;
    public order: number | null
    public role: string | null;
    public title: string | null;
    public twitterLink: string | null;
    public websiteLink: string | null;

    constructor(props: CastCrewDataProps) {
        this.id = props.id ?? null;

        this.firstName = props.firstName ?? null;
        this.lastName = props.lastName ?? null;
        this.fullName = props.fullName || `${this.firstName ?? ''} ${this.lastName ?? ''}`.trim();

        this.birthday = props.birthday ?? null;
        this.hometown = props.hometown ?? null;
        this.funFact = props.funFact ?? null;

        this.memberType = props.memberType ?? null;
        this.role = props.role ?? null;
        this.order = typeof props.order === 'number' || typeof props.order === 'string'
            ? (parseInt(props.order as string, 10) || 0)
            : null;

        this.facebookLink = props.facebookLink ?? null;
        this.instagramLink = props.instagramLink ?? null;
        this.twitterLink = props.twitterLink ?? null;
        this.websiteLink = props.websiteLink ?? null;
    }

    public static fromRecord(record: Record<string, unknown>): CastCrewData
    {
        const birthday = record?.birthday as string | null | undefined;
        const facebookLink = record?.facebook_link as string | null | undefined;
        const firstName = record?.firstName as string | null | undefined;
        const fullName = record?.fullName as string | null | undefined;
        const funFact = record?.funFact as string | null | undefined;
        const hometown = record?.hometown as string | null | undefined;
        const id = (record?.id || record?.ID) as string | number | null | undefined;
        const instagramLink = record?.instagramLink as string | null | undefined;
        const lastName = record?.lastName as string | null | undefined;
        const twitterLink = record?.twitterLink as string | null | undefined;
        const memberType = record?.memberType as string | null | undefined;
        const type = record?.type as string | null | undefined;
        const websiteLink = record?.websiteLink as string | null | undefined;
        const selfTitle = record?.selfTitle as string | null | undefined;
        const title = record?.title as string | null | undefined;

        return new CastCrewData({
            birthday: birthday || null,
            facebookLink: facebookLink || null,
            firstName: firstName || null,
            fullName: fullName || `${firstName || ''} ${lastName || ''}`.trim() || null,
            funFact: funFact || null,
            hometown: hometown || null,
            id: id || null,
            instagramLink: instagramLink || null,
            lastName: lastName || null,
            memberType: memberType || type || null,
            order: record?.order as string | number | null | undefined,
            role: record?.role as string | null | undefined,
            title: selfTitle || title || null,
            twitterLink: twitterLink || null,
            websiteLink: websiteLink || null,
        });
    }

    public static fromEntity(entity: CastCrewEntity): CastCrewData {
        const firstName = entity?.meta?._ccwp_cast_crew_name_first || null;
        const lastName = entity?.meta?._ccwp_cast_crew_name_last || null;
        const fullName = `${firstName || ''} ${lastName || ''}`.trim() || null;
        const title = entity.title?.raw || null;
        const selfTitle = entity?.meta?._ccwp_cast_crew_self_title || null;

        return new CastCrewData({
            birthday: entity?.meta?._ccwp_cast_crew_birthday || null,
            facebookLink: entity?.meta?._ccwp_cast_crew_facebook_link || null,
            firstName,
            fullName: fullName || title || null,
            funFact: entity?.meta?._ccwp_cast_crew_fun_fact || null,
            hometown: entity?.meta?._ccwp_cast_crew_hometown || null,
            id: entity?.id || null,
            instagramLink: entity?.meta?._ccwp_cast_crew_instagram_link || null,
            lastName,
            title: selfTitle || null,
            twitterLink: entity?.meta?._ccwp_cast_crew_twitter_link || null,
            websiteLink: entity?.meta?._ccwp_cast_crew_website_link || null,
        });
    }
}
