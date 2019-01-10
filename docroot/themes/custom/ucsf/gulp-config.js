module.exports = {
  tasks: {
    css: {
      enabled: true,
      type: 'default',
      src: [
        'scss/**/*.scss',
        'components/**/**/*.scss',
        'components/**/*.scss'
      ],
      dest: './assets/css',
      outputStyle: 'nested',
      includePaths: [],
      autoPrefixerBrowsers: [
        'last 2 versions',
        '>= 1%',
        'ie >= 11'
      ],
      removeSourceComments: true,
      flattenDest: true,
      lint: false,
    },
    js: {
      enabled: true,
      type: 'default',
      src: [
        'js/**/*.js'
      ],
      dest: './assets/js',
      concat: {
        enabled: false,
        dest: 'all.js'
      },
      babel: true,
      uglify: false,
      lint: false
    },
    images: {
      enabled: true,
      type: 'default',
      src: [
        'images/**/*{.png,.gif,.jpg,.jpeg,.svg}'
      ],
      dest: './assets/images',
      flattenDest: false
    },
    icons: {
      enabled: true,
      type: 'default',
      src: [
        'sprite/**/*.svg'
      ],
      dest: './assets/images',
      destName: 'sprite.svg'
    },
    patternLab: {
      enabled: false,
      type: 'default',
      basePath: './pattern-lab',
      watchedExtensions: ['twig', 'yml', 'yaml', 'json', 'md'],
      scssToYml: []
    }
  },
  browserSync: {
    enabled: true,
    baseDir: './',
    startPath: '',
    domain: 'http://ucsf-main.local',
    startupBehavior: false,
    ui: false,
  }
};
