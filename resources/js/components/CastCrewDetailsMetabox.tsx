import React, {FC, useState} from 'react';
import {TextControl} from '@wordpress/components';
import useHasPostTitle from '../hooks/useHasPostTitle';
import {dateToFormat} from '../utils/dates';
import {updatePostTitle} from '../utils/post';
import DatePickerControl from './DatePickerControl';
import {CastCrewDetails} from '../types/metaboxes';

export type CastCrewDetailsMetaboxProps = {
    initialDetails?: CastCrewDetails | null | undefined;
};

const CastCrewDetailsMetabox: FC<CastCrewDetailsMetaboxProps> = ({initialDetails}) => {
    const hasPostTitle = useHasPostTitle();
    const [state, setState] = useState<CastCrewDetails>({
        ID: initialDetails?.ID || 0,
        name_first: initialDetails?.name_first || '',
        name_last: initialDetails?.name_last || '',
        self_title: initialDetails?.self_title || '',
        birthday: dateToFormat(initialDetails?.birthday, {input: 'yyyy-MM-dd', output: 'MM/dd/yyyy'}) || '',
        hometown: initialDetails?.hometown || '',
        website_link: initialDetails?.website_link || '',
        facebook_link: initialDetails?.facebook_link || '',
        twitter_link: initialDetails?.twitter_link || '',
        instagram_link: initialDetails?.instagram_link || '',
        fun_fact: initialDetails?.fun_fact || '',
    });

    const setFirstName = (value: string) => {
        setState(current => ({...current, name_first: value}));
        if (!hasPostTitle) {
            updatePostTitle(`${value || ''} ${state.name_last || ''}`.trim());
        }
    };
    const setLastName = (value: string) => {
        setState(current => ({...current, name_last: value}));
        if (!hasPostTitle) {
            updatePostTitle(`${state.name_first || ''} ${value || ''}`.trim());
        }
    };
    const setTitle = (value: string) => setState(current => ({...current, self_title: value}));
    const setBirthday = (value: string) => setState(current => ({...current, birthday: value}));
    const setHometown = (value: string) => setState(current => ({...current, hometown: value}));
    const setWebsiteLink = (value: string) => setState(current => ({...current, website_link: value}));
    const setFacebookLink = (value: string) => setState(current => ({...current, facebook_link: value}));
    const setTwitterLink = (value: string) => setState(current => ({...current, twitter_link: value}));
    const setInstagramLink = (value: string) => setState(current => ({...current, instagram_link: value}));
    const setFunFact = (value: string) => setState(current => ({...current, fun_fact: value}));

    return (
        <div className="ccwp-react-metabox">
            <TextControl
                label="First Name*"
                value={state.name_first}
                onChange={setFirstName}
                name="ccwp_name_first"
                help={
                    '*Required. These fields are used to auto-generate the post title with ' +
                    "the cast or crew member's full name."
                }
            />
            <TextControl
                label="Last Name*"
                value={state.name_last}
                onChange={setLastName}
                name="ccwp_name_last"
            />
            <TextControl
                label="Title*"
                value={state.self_title}
                onChange={setTitle}
                name="ccwp_self_title"
                help={
                    '*Required. If the cast or crew member has many roles across different productions, ' +
                    'try to use the one they identify with the most. Ex. Director, Actor, Choreographer, etc.'
                }
            />
            <DatePickerControl
                label="Birthday"
                name="ccwp_birthday"
                onChange={setBirthday}
                value={state.birthday}
            />
            <TextControl
                label="Hometown"
                value={state.hometown}
                onChange={setHometown}
                name="ccwp_hometown"
            />
            <TextControl
                label="Website Link"
                value={state.website_link}
                onChange={setWebsiteLink}
                name="ccwp_website_link"
            />
            <TextControl
                label="Facebook Link"
                value={state.facebook_link}
                onChange={setFacebookLink}
                name="ccwp_facebook_link"
            />
            <TextControl
                label="Twitter Link"
                value={state.twitter_link}
                onChange={setTwitterLink}
                name="ccwp_twitter_link"
            />
            <TextControl
                label="Instagram Link"
                value={state.instagram_link}
                onChange={setInstagramLink}
                name="ccwp_instagram_link"
            />
            <TextControl
                label="Fun Fact"
                value={state.fun_fact}
                onChange={setFunFact}
                name="ccwp_fun_fact"
                help="This should be kept to one sentence."
            />
        </div>
    );
};

CastCrewDetailsMetabox.displayName = 'CastCrewDetailsMetabox';

export default CastCrewDetailsMetabox;
