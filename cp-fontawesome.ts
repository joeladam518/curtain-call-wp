import fs from 'node:fs';
import path from 'node:path';
import {fileURLToPath} from 'node:url';
import {type Plugin} from 'rollup';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename)

export type CopyFontAwesomeFnProps = {
    outDir: string;
}

export default function copyFontAwesome({
    outDir
}: CopyFontAwesomeFnProps): Plugin {
    return {
        name: 'copy-fontawesome',
        async writeBundle() {
            const src = path.resolve(__dirname, 'node_modules/@fortawesome/fontawesome-free/webfonts');
            const dest = path.resolve(outDir, 'fonts/fontawesome');

            if (fs.existsSync(src)) {
                if (!fs.existsSync(dest)) {
                    fs.mkdirSync(dest, {recursive: true});
                }
                fs.cpSync(src, dest, {recursive: true});
            }
        },
    };
}
