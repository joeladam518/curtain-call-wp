import {registerPlugin} from '@wordpress/plugins';
import Sidebar, {Icon} from '../components/Sidebar';

registerPlugin('ccwp-sidebar', { icon: Icon, render: Sidebar });
