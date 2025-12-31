import Entity from './entity';
import Post from './post';

export type ProductionEntityMeta = {
    _ccwp_production_date_end: string;
    _ccwp_production_date_start: string;
    _ccwp_production_name: string;
    _ccwp_production_show_times: string;
    _ccwp_production_ticket_url: string;
    _ccwp_production_venue: string;
}

export interface ProductionPost extends Post {
    post_type: 'ccwp_production';
}

export interface ProductionEntity extends Entity {
    meta?: ProductionEntityMeta;
}
