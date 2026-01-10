import {createRoot} from '@wordpress/element';
import CastCrewProductionsMetabox from '../components/CastCrewProductionsMetabox';
import CastCrewDetailsMetabox from '../components/CastCrewDetailsMetabox';
import {CastCrewDetails} from '../types/metaboxes';

document.addEventListener('DOMContentLoaded', () => {
    const castCrewDetailsRoot = document.getElementById('ccwp-cast-crew-details-react-root');
    if (castCrewDetailsRoot) {
        const root = createRoot(castCrewDetailsRoot);
        root?.render(
            <CastCrewDetailsMetabox
                initialDetails={window?.CCWP_DATA?.initialDetails as CastCrewDetails | undefined}
            />
        );
    }

    const castCrewProductionsRoot = document.getElementById('ccwp-cast-crew-productions-react-root');
    if (castCrewProductionsRoot) {
        const root = createRoot(castCrewProductionsRoot);
        root?.render(
            <CastCrewProductionsMetabox
                castCrewId={window?.CCWP_DATA?.initialDetails?.ID ?? null}
                options={window?.CCWP_DATA?.options ?? []}
                productions={window?.CCWP_DATA?.productions ?? []}
            />
        );
    }
});
