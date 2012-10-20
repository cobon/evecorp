?><?php
/*
 * Eve Online Plugin for WordPress
 *
 * Copyright © 2012 Mitome Cobon-Han  (mitome.ch@gmail.com)
 *
 *	This file is part of evecorp.
 *
 *	evecorp is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *
 *	evecorp is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *	You should have received a copy of the GNU General Public License
 *	along with evecorp.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package evecorp
 *
 * @todo: Remove e plugin related user options
 * @todo: Remove e plugin related user meta data
 *
 */

//if uninstall not called from WordPress exit
if( !defined( 'ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') )
    exit();


// Remove options
delete_option('evecorp_options');
?><?php
