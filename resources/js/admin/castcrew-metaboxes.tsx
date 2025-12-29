import {render} from '@wordpress/element';
import CastCrewProductionsMetabox from '../components/CastCrewProductionsMetabox';
import CastCrewDetailsMetabox from '../components/CastCrewDetailsMetabox';
import {CastCrewDetails} from '../types/metaboxes';

document.addEventListener('DOMContentLoaded', () => {
    const castCrewDetailsRoot = document.getElementById('ccwp-cast-crew-details-react-root');
    if (castCrewDetailsRoot) {
        render(
            <CastCrewDetailsMetabox
                initialDetails={window?.CCWP_DATA?.initialDetails as CastCrewDetails | undefined}
            />,
            castCrewDetailsRoot
        );
    }

    const castCrewProductionsRoot = document.getElementById('ccwp-cast-crew-productions-react-root');
    if (castCrewProductionsRoot) {
        render(
            <CastCrewProductionsMetabox
                castCrewId={window?.CCWP_DATA?.initialDetails?.ID ?? null}
                options={window?.CCWP_DATA?.options ?? []}
                productions={window?.CCWP_DATA?.productions ?? []}
            />,
            castCrewProductionsRoot
        );
    }
});
