import Entity from './entity';
import Post from './post';

export type CastCrewPostMeta = {
    _ccwp_cast_crew_birthday: string;
    _ccwp_cast_crew_facebook_link: string;
    _ccwp_cast_crew_fun_fact: string;
    _ccwp_cast_crew_hometown: string;
    _ccwp_cast_crew_instagram_link: string;
    _ccwp_cast_crew_name_first: string;
    _ccwp_cast_crew_name_last: string;
    _ccwp_cast_crew_self_title: string;
    _ccwp_cast_crew_twitter_link: string;
    _ccwp_cast_crew_website_link: string;
};

export interface CastCrewPost extends Post {
    post_type: 'ccwp_cast_and_crew';
}

export interface CastCrewEntity extends Entity {
    meta?: CastCrewPostMeta;
}
