/**
 * Browserless.io puppeteer endpoint (for more granular control)
 * HTML generated internally and loaded into puppeteer
 *
 * For running locally (puppeteer.launch), the following packages are needed on top of standard WSL2-Ubuntu:
 * sudo apt-get install libnss3-dev libxkbcommon0 libgbm1
 * sudo apt-get install -y gconf-service libasound2 libatk1.0-0 libc6 libcairo2 libcups2 libdbus-1-3 libexpat1 libfontconfig1 libgcc1 libgconf-2-4 libgdk-pixbuf2.0-0 libglib2.0-0 libgtk-3-0 libnspr4 libpango-1.0-0 libpangocairo-1.0-0 libstdc++6 libx11-6 libx11-xcb1 libxcb1 libxcomposite1 libxcursor1 libxdamage1 libxext6 libxfixes3 libxi6 libxrandr2 libxrender1 libxss1 libxtst6 ca-certificates fonts-liberation libappindicator1 libnss3 lsb-release xdg-utils wget libgbm-dev
 */

import puppeteer from 'puppeteer-core'
const { performance } = require('perf_hooks')
const { URL } = require('url')
const { loadNuxt, build } = require('nuxt')
const { Router } = require('express')
const { memoryUsage } = require('process')

const router = Router()

let lastTime = null
function measurePerformance(msg) {
  const now = performance.now()
  const memory = memoryUsage()

  if (lastTime !== null) {
    console.log(
      `(took ${Math.round(now - lastTime)} millisecons / using ${
        memory.heapUsed / 1000000
      }MB)`
    )
  }
  lastTime = now

  console.log('\n')
  console.log(msg)
}

// Test route
router.use('/pdf', async (req, res) => {
  let browser = null

  try {
    measurePerformance('Building Nuxt if necessary... ' + process.env.NODE_ENV)

    const isDev = process.env.NODE_ENV !== 'production'
    // Get nuxt instance for start (production mode)
    const nuxt = await loadNuxt(isDev ? 'dev' : 'start')

    // Enable live build & reloading on dev
    if (isDev) {
      await build(nuxt)
    }

    // Capture HTML via internal Nuxt render call
    measurePerformance('Rendering page in Nuxt...')
    const url = new URL(req.url, `http://${req.headers.host}`)
    const queryString = url.search
    const { html } = await nuxt.renderRoute('/' + queryString, { req }) // pass `req` object to Nuxt will also pass authentication cookies automatically

    measurePerformance('Connecting to puppeteer...')

    // Connect to browserless.io (puppeteer websocket)
    browser = await puppeteer.connect({
      browserWSEndpoint: process.env.BROWSER_WS_ENDPOINT,
    })

    const page = await browser.newPage()

    /**
     * Debugging puppeteer
     */
    /*
    page.on('request', (request) =>
      console.log('>>', request.method(), request.url())
    )
    page.on('response', (response) =>
      console.log('<<', response.status(), response.url())
    )
    page.on('error', (err) => {
      console.log('error happen at the page: ', err)
    })
    page.on('pageerror', (pageerr) => {
      console.log('pageerror occurred: ', pageerr)
    }) */

    // set HTML content of current page
    measurePerformance('Puppeteer set HTML content & load resources...')
    page.setUserAgent(
      'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:96.0) Gecko/20100101 Firefox/96.0'
    )
    page.setContent(html)

    /**
     * Following code snippets copied mostly from https://gitlab.pagedmedia.org/tools/pagedjs-cli/-/blob/master/src/printer.js
     */

    measurePerformance('Setup hooks...')

    // create Promise to wait for PagedJS 'after'
    let resolver
    const pagedjsRendered = new Promise(function (resolve) {
      resolver = resolve
    })
    await page.exposeFunction('onRendered', () => {
      resolver() // resolve promise
    })

    // autostart of PagedJS and after-event
    page.evaluate(() => {
      window.PagedConfig = window.PagedConfig || {}
      window.PagedConfig.auto = true
      window.PagedConfig.after = () => {
        window.onRendered()
      }
    })

    // add PagedJS
    measurePerformance('Add PagedJS...')
    page.addScriptTag({
      url: 'https://unpkg.com/pagedjs/dist/paged.polyfill.js',
    })

    // alternative: add local pagedjs package
    /*
    const pagedjsLocation = require.resolve('pagedjs/dist/paged.polyfill.js')
    const paths = pagedjsLocation.split('node_modules')
    const scriptPath = paths[0] + 'node_modules' + paths[paths.length - 1]
    page.addScriptTag({
      path: scriptPath,
    }) */

    // wait for Promise to fire
    measurePerformance('Wait for PagedJS to render...')
    await pagedjsRendered

    // print pdf
    measurePerformance('Generate PDf...')
    const pdf = await page.pdf({
      displayHeaderFooter: false,
      printBackground: true,
      format: 'A4',
      margin: {
        bottom: '0px',
        left: '0px',
        right: '0px',
        top: '0px',
      },
    })

    measurePerformance()
    browser.close()

    res.contentType('application/pdf')
    res.send(pdf)
  } catch (error) {
    console.error({ error }, 'Something happened!')
    browser.close()
    res.send(error)
  }
})

module.exports = router
