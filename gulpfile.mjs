/**
 * Gulpfile
 *
 * @author Space-Time Inc.
 * @version 2022-12-09
 */

const SUB_REPS = [
	'alt',
	'blok',
	'dia',
	'medi',
	'meta',
	'navi',
	'plex',
	'post',
	'ref',
	'socio',
	'sys',
	'taxo',
];

import gulp from 'gulp';

import { pkgDir } from './gulp/common.mjs';
import { makeCopyTask } from './gulp/task-copy.mjs';

const copy_s = SUB_REPS.map(e => makeCopyTask(`${pkgDir(`wpinc-${e}`)}/dist/**/*`, `./src/${e}/`));
const copy = makeCopyTask('src/**/*', './dist/');

export const update = gulp.parallel(...copy_s);
export default gulp.parallel(copy);
