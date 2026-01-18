import {createRoot} from '@wordpress/element';
import ProductionDetailsMetabox from '../components/ProductionDetailsMetabox';
import ProductionCastCrewMetabox from '../components/ProductionCastCrewMetabox';
import {ProductionDetails} from '../types/metaboxes';

document.addEventListener('DOMContentLoaded', () => {
    const productionDetailsRoot = document.getElementById('ccwp-production-details-react-root');
    if (productionDetailsRoot) {
        const root = createRoot(productionDetailsRoot);
        root?.render(
            <ProductionDetailsMetabox
                initialDetails={window?.CCWP_DATA?.initialDetails as ProductionDetails | undefined}
            />
        );
    }

    const productionCastCrewRoot = document.getElementById('ccwp-production-cast-crew-react-root');
    if (productionCastCrewRoot) {
        const root = createRoot(productionCastCrewRoot);
        root?.render(
            <ProductionCastCrewMetabox
                productionId={window?.CCWP_DATA?.initialDetails?.ID ?? null}
                options={window?.CCWP_DATA?.options ?? []}
                cast={window?.CCWP_DATA?.cast ?? []}
                crew={window?.CCWP_DATA?.crew ?? []}
            />
        );
    }
});
