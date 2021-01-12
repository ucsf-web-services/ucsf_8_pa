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
      autoPrefixer: {
        browers: [
          'last 5 versions',
          '>= 1%',
          'IE >= 11',
          'Safari >= 7',
          'Firefox 14'
        ],
        grid: false
        //strict: "grid"
      },
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
    enabled: false,
    baseDir: './',
    startPath: '',
    domain: 'http://local.ucsf_mag.edu',
    startupBehavior: false,
    ui: false,
  }
};
