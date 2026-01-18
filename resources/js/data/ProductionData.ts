import {ProductionEntity} from '../types/production';

type ProductionDataProps = {
    endDate?: string | null;
    id?: string | number | null;
    name?: string | null;
    showTimes?: string | null;
    startDate?: string | null;
    ticketUrl?: string | null;
    venue?: string | null;
    memberType?: string | null;
    role?: string | null;
    order?: string | number | null;
};

export default class ProductionData {
    public endDate: string | null;
    public id: string | number | null;
    public name: string | null;
    public showTimes: string | null;
    public startDate: string | null;
    public ticketUrl: string | null;
    public venue: string | null;
    public memberType: string | null;
    public role: string | null;
    public order: number | null;

    constructor(props: ProductionDataProps) {
        this.id = props.id ?? null;
        this.name = props.name ?? null;
        this.endDate = props.endDate ?? null;
        this.startDate = props.startDate ?? null;
        this.showTimes = props.showTimes ?? null;
        this.ticketUrl = props.ticketUrl ?? null;
        this.venue = props.venue ?? null;
        this.memberType = props.memberType ?? null;
        this.role = props.role ?? null;
        this.order = typeof props.order === 'number' || typeof props.order === 'string'
            ? (parseInt(props.order as string, 10) || 0)
            : null;
    }

    public static fromRecord(record: Record<string, unknown>): ProductionData {
        const endDate = record?.endDate as string | null | undefined;
        const id = (record?.id || record?.ID) as string | number | null | undefined;
        const name = record?.name as string | null | undefined;
        const showTimes = record?.showTimes as string | null | undefined;
        const startDate = record?.startDate as string | null | undefined;
        const ticketUrl = record?.ticketUrl as string | null | undefined;
        const venue = record?.venue as string | null | undefined;
        const memberType = record?.memberType as string | null | undefined;
        const type = record?.type as string | null | undefined;
        const role = record?.role as string | null | undefined;
        const order = record?.order as string | number | null | undefined;

        return new ProductionData({
            endDate: endDate || null,
            id: id || null,
            name: name || null,
            showTimes: showTimes || null,
            startDate: startDate || null,
            ticketUrl: ticketUrl || null,
            venue: venue || null,
            memberType: memberType || type || null,
            role: role || null,
            order: order || null,
        });
    }

    public static fromEntity(entity: ProductionEntity): ProductionData {
        const metaName = entity?.meta?._ccwp_production_name || null;
        const title = entity.title?.raw || null;

        return new ProductionData({
            endDate: entity?.meta?._ccwp_production_date_end || null,
            id: entity?.id || null,
            name: metaName ?? title ?? null,
            showTimes: entity?.meta?._ccwp_production_show_times || null,
            startDate: entity?.meta?._ccwp_production_date_start || null,
            ticketUrl: entity?.meta?._ccwp_production_ticket_url || null,
            venue: entity?.meta?._ccwp_production_venue || null,
        });
    }
}
