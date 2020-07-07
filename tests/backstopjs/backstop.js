let {urls, advanced} = require('./scenarios.json')

// Load optional local scenarios.
try {
  let localScenarios = require('./scenarios.local.json')

  if (localScenarios.url !== undefined) {
    urls = [...urls, ...localScenarios.url]
  }
  if (localScenarios.advanced !== undefined) {
    advanced = [...advanced, ...localScenarios.advanced]
  }
} catch (ex) {
  console.log('You may create a scenarios.local.json file for tests specific to your computer.')
}

const baseUrl = 'http://appserver.ucsf.internal'

urls = urls.map(url => {
  const data = {}

  data.label = url
  // Prepend the base url if not provided.
  if (!url.includes(baseUrl)) {
    data.url = baseUrl + '/' + url
  } else {
    data.url = url
  }
  return data
})

advanced = advanced.map(data => {
  // Add a label based on the Url if one is not provided.
  if (!data.label) {
    data.label = data.url
  }
  // Prepend the base url if not provided.
  if (!data.url.includes(baseUrl)) {
    data.url = baseUrl + '/' + data.url
  }
  return data
})

const scenarios = [...urls, ...advanced]

module.exports = {
  id: 'ucsf',
  url: 'http://appserver.ucsf.internal',
  viewports: [
    {
      label: 'phone',
      width: 320,
      height: 480,
    },
    {
      label: 'tablet',
      width: 1024,
      height: 768,
    },
    {
      label: 'desktop',
      width: 1920,
      height: 1080,
    },
  ],
  onBeforeScript: 'onBefore.js',
  onReadyScript: 'onReady.js',
  scenarios: scenarios,
  misMatchThreshold: 0.5,
  paths: {
    bitmaps_reference: `${__dirname}/reference`,
    bitmaps_test: `${__dirname}/runs`,
    engine_scripts: `${__dirname}/scripts`,
    html_report: `${__dirname}/reports/html`,
    ci_report: `${__dirname}/reports/ci`,
  },
  report: ['browser'],
  engine: 'puppeteer',
  engineFlags: [],
  engineOptions: {
    ignoreHTTPSErrors: true,
    args: [
      '--no-sandbox',
      '--disable-setuid-sandbox',
    ],
  },
  asyncCaptureLimit: 5,
  asyncCompareLimit: 15,
  debug: false,
  debugWindow: false,
};
