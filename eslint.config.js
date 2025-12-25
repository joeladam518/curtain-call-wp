import eslint from '@eslint/js';
import tseslint from 'typescript-eslint';
import reactPlugin from 'eslint-plugin-react';
import reactHooksPlugin from 'eslint-plugin-react-hooks';
import stylistic from '@stylistic/eslint-plugin';
import globals from 'globals';

export default tseslint.config(
    {
        files: [
            './resources/**/*.{ts,tsx}'
        ]
    },
    {
        ignores: [
            '.mariadb/',
            '.rollup.cache/ ',
            'docker/',
            'node_modules/',
            'public/',
            'plugin/',
            'vendor/',
            'eslint.config.js'
        ]
    },
    eslint.configs.recommended,
    ...tseslint.configs.recommended,
    {
        plugins: {
            'react': reactPlugin,
            'react-hooks': reactHooksPlugin,
            '@stylistic': stylistic,
        },
        languageOptions: {
            parserOptions: {
                ecmaFeatures: {
                    jsx: true,
                },
            },
            globals: {
                ...globals.browser,
            },
        },
        settings: {
            react: {
                version: '18.3.1',
            },
        },
        rules: {
            ...reactPlugin.configs.recommended.rules,
            ...reactHooksPlugin.configs.recommended.rules,
            'react/react-in-jsx-scope': 'off', // Not needed in modern React
            '@stylistic/array-bracket-newline': ['error', 'consistent'],
            '@stylistic/array-bracket-spacing': ['error', 'never'],
            '@stylistic/array-element-newline': ['error', {
                ArrayExpression: 'consistent',
                ArrayPattern: {minItems: 4},
            }],
            '@stylistic/arrow-parens': ['error', 'as-needed', {
                requireForBlockBody: true,
            }],
            '@stylistic/arrow-spacing': ['error', {before: true, after: true}],
            '@stylistic/block-spacing': ['error', 'never'],
            '@stylistic/brace-style': ['error', '1tbs', {allowSingleLine: false}],
            '@stylistic/comma-dangle': ['error', {
                arrays: 'always-multiline',
                enums: 'always-multiline',
                exports: 'always-multiline',
                functions: 'only-multiline',
                imports: 'always-multiline',
                objects: 'always-multiline',
                tuples: 'always-multiline',
            }],
            '@stylistic/comma-spacing': ['error', {before: false, after: true}],
            '@stylistic/comma-style': ['error', 'last'],
            '@stylistic/computed-property-spacing': ['error', 'never'],
            '@stylistic/dot-location': ['error', 'property'],
            '@stylistic/eol-last': ['error', 'always'],
            '@stylistic/function-call-spacing': ['error', 'never'],
            '@stylistic/function-paren-newline': ['error', 'multiline-arguments'],
            '@stylistic/generator-star-spacing': ['error', {before: true, after: false}],
            '@stylistic/indent': ['warn', 4, {
                SwitchCase: 1,
            }],
            '@stylistic/jsx-quotes': ['error', 'prefer-double'],
            '@stylistic/key-spacing': ['error', {beforeColon: false, afterColon: true}],
            '@stylistic/keyword-spacing': ['error', {before: true, after: true}],
            '@stylistic/linebreak-style': ['error', 'unix'],
            '@stylistic/lines-between-class-members': ['error', {
                enforce: [
                    {blankLine: 'always', prev: 'field', next: 'method'},
                    {blankLine: 'always', prev: 'method', next: 'method'},
                ],
            }],
            '@stylistic/max-len': ['error', 120, {
                ignorePattern: '^import',
                ignoreUrls: true,
            }],
            '@stylistic/max-statements-per-line': ['error', {max: 1}],
            '@stylistic/member-delimiter-style': ['error', {
                'multiline': {
                    'delimiter': 'semi',
                    'requireLast': true,
                },
                'singleline': {
                    'delimiter': 'semi',
                    'requireLast': false,
                },
                'multilineDetection': 'brackets',
            }],
            '@stylistic/multiline-ternary': ['error', 'always-multiline'],
            '@stylistic/new-parens': ['error', 'always'],
            '@stylistic/no-extra-semi': 'error',
            '@stylistic/no-floating-decimal': 'error',
            '@stylistic/no-mixed-spaces-and-tabs': 'error',
            '@stylistic/no-multi-spaces': 'error',
            '@stylistic/no-multiple-empty-lines': ['error', {
                max: 2,
                maxEOF: 1,
                maxBOF: 0,
            }],
            '@stylistic/no-trailing-spaces': 'error',
            '@stylistic/no-whitespace-before-property': 'error',
            '@stylistic/object-curly-newline': ['error', {consistent: true}],
            '@stylistic/object-curly-spacing': ['error', 'never'],
            '@stylistic/one-var-declaration-per-line': ['error', 'always'],
            '@stylistic/operator-linebreak': ['error', 'after', {
                overrides: {
                    '?': 'before',
                    ':': 'before',
                },
            }],
            '@stylistic/padded-blocks': ['error', 'never'],
            '@stylistic/quote-props': ['error', 'as-needed'],
            '@stylistic/quotes': ['error', 'single', {
                avoidEscape: true,
                allowTemplateLiterals: 'never',
            }],
            '@stylistic/rest-spread-spacing': ['error', 'never'],
            '@stylistic/semi': ['error', 'always'],
            '@stylistic/semi-spacing': ['error', {before: false, after: true}],
            '@stylistic/space-before-blocks': ['error', 'always'],
            '@stylistic/space-before-function-paren': ['error', {
                anonymous: 'never',
                named: 'never',
                asyncArrow: 'always',
            }],
            '@stylistic/space-in-parens': ['error', 'never'],
            '@stylistic/space-infix-ops': ['error', {int32Hint: true}],
            '@stylistic/space-unary-ops': ['error', {
                words: true,
                nonwords: false,
            }],
            '@stylistic/switch-colon-spacing': ['error', {after: true, before: false}],
            '@stylistic/template-curly-spacing': ['error', 'never'],
            '@stylistic/template-tag-spacing': ['error', 'never'],
            '@stylistic/wrap-iife': ['error', 'inside'],
            '@stylistic/yield-star-spacing': ['error', {before: true, after: false}],
            '@stylistic/type-annotation-spacing': ['error', {
                before: false,
                after: true,
                overrides: {
                    arrow: {
                        before: true,
                        after: true,
                    },
                },
            }],
            '@stylistic/jsx-closing-bracket-location': ['error', 'line-aligned'],
            '@stylistic/jsx-closing-tag-location': 'error',
            '@stylistic/jsx-curly-brace-presence': ['error', 'never'],
            '@stylistic/jsx-curly-newline': ['error', 'consistent'],
            '@stylistic/jsx-curly-spacing': ['error', 'never'],
            '@stylistic/jsx-equals-spacing': ['error', 'never'],
            '@stylistic/jsx-first-prop-new-line': ['error', 'multiline'],
            '@stylistic/jsx-indent-props': ['error', 4],
            '@stylistic/jsx-max-props-per-line': ['error', {
                maximum: {
                    single: 2,
                    multi: 1,
                },
            }],
            '@stylistic/jsx-self-closing-comp': ['error', {component: true, html: true}],
            '@stylistic/jsx-wrap-multilines': ['error', {
                declaration: 'parens-new-line',
            }],
            '@typescript-eslint/consistent-type-definitions': 'off',
            '@typescript-eslint/no-explicit-any': 'off',
            '@typescript-eslint/no-inferrable-types': 'off',
            'curly': ['error', 'all'],
            'eqeqeq': ['error', 'always'],
            'no-console': ['warn', {allow: ['warn', 'error']}],
            'indent': 'off',
            '@typescript-eslint/indent': 'off'
        },
    },
    {
        files: ['./resources/**/*.tsx'],
        rules: {
            '@stylistic/multiline-ternary': 'off',
        }
    },
    {
        files: ['**/*.d.ts'],
        rules:{
            '@typescript-eslint/consistent-type-definitions': 'off',
            '@typescript-eslint/no-unused-vars': 'off',
            '@typescript-eslint/no-empty-object-type': 'off',
        },
    },
    {
        files: [
            'postcss.config.js',
            'tailwind.config.ts',
            'vite.config.ts',
        ],
        languageOptions: {
            globals: {
                ...globals.node,
                ...globals.es2022,
            },
        },
        rules: {
            '@typescript-eslint/consistent-type-definitions': 'off',
            '@typescript-eslint/no-require-imports': 'off',
            '@typescript-eslint/no-var-requires': 'off',
        },
    },
);
