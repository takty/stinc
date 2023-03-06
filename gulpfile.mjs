/**
 * Gulpfile
 *
 * @author Space-Time Inc.
 * @version 2023-03-06
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

import { makeCopyTask } from './gulp/task-copy.mjs';

export const update = async done => {
	const { pkgDir } = await import('./gulp/common.mjs');
	SUB_REPS.map(e => makeCopyTask(`${pkgDir(`wpinc-${e}`)}/dist/**/*`, `./src/${e}/`)());
	done();
};
export default gulp.parallel(makeCopyTask('src/**/*', './dist/'));
