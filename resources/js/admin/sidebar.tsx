import {registerPlugin} from '@wordpress/plugins';
import Sidebar from '../components/Sidebar';
import TheatreCurtains from '../icons/TheatreCurtains';

registerPlugin('ccwp-sidebar', { icon: TheatreCurtains, render: Sidebar });
