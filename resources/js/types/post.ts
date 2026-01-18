import PostStatus from '../enums/PostStatus';

/*
    ID                    bigint unsigned auto_increment                primary key,
    post_author           bigint unsigned default 0                     not null,
    post_date             datetime        default '0000-00-00 00:00:00' not null,
    post_date_gmt         datetime        default '0000-00-00 00:00:00' not null,
    post_content          longtext                                      not null,
    post_title            text                                          not null,
    post_excerpt          text                                          not null,
    post_status           varchar(20)     default 'publish'             not null,
    comment_status        varchar(20)     default 'open'                not null,
    ping_status           varchar(20)     default 'open'                not null,
    post_password         varchar(255)    default ''                    not null,
    post_name             varchar(200)    default ''                    not null,
    to_ping               text                                          not null,
    pinged                text                                          not null,
    post_modified         datetime        default '0000-00-00 00:00:00' not null,
    post_modified_gmt     datetime        default '0000-00-00 00:00:00' not null,
    post_content_filtered longtext                                      not null,
    post_parent           bigint unsigned default 0                     not null,
    guid                  varchar(255)    default ''                    not null,
    menu_order            int             default 0                     not null,
    post_type             varchar(20)     default 'post'                not null,
    post_mime_type        varchar(100)    default ''                    not null,
    comment_count         bigint          default 0                     not null
 */

interface Post {
    ID: number;
    post_author: number;
    post_date: string;
    post_date_gmt: string;
    post_content: string;
    post_title: string;
    post_excerpt: string;
    post_status: PostStatus;
    comment_status: string;
    ping_status: string;
    post_password: string;
    post_name: string;
    to_ping: string;
    pinged: string;
    post_modified: string;
    post_modified_gmt: string;
    post_content_filtered: string;
    post_parent: number;
    guid: string;
    menu_order: number;
    post_type: string;
    post_mime_type: string;
    comment_count: number;
}

export default Post;
