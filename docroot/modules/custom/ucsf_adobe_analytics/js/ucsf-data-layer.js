

// Ensure EpicPx exists

if (!window.EpicPx) {
    window.EpicPx = {};
}

// Ensure ReactContext exists within EpicPx
if (!window.EpicPx.ReactContext) {
    window.EpicPx.ReactContext = {};
}

// Ensure user exists within ReactContext
if (!window.EpicPx.ReactContext.user) {
    window.EpicPx.ReactContext.user = {};
}

EpicPx.ReactContext.user.username = "mychartloggedinusername";

window.adobeDataLayer = window.adobeDataLayer || [];

function detectDeviceType() {
    var userAgent = navigator.userAgent || navigator.vendor || window.opera;

    // Mobile
    if (/Mobi|Android/i.test(userAgent)) {
        if (/tablet|ipad|playbook|silk/i.test(userAgent)) {
            return 'tablet'; // Tablets
        } else {
            return 'mobile'; // Mobile phones
        }
    }

    // eReader
    if (/kindle|nook|kobo|sony\sreader/i.test(userAgent)) {
        return 'ereader';
    }

    // Gaming
    if (/playstation|psp|nintendo\s+\w+\s+browser|xbox/i.test(userAgent)) {
        return 'gaming';
    }

    // Television and Settop Boxes
    if (/smart-tv|google.tv|vizio|smart[- ]?dtv|netcast|webtv|opera tv|hbbtv|pov_tv|nettv/i.test(userAgent)) {
        return 'television';
    }
    if (/apple\s?tv|roku|dvr|directv|hopper|set-top box|set-top-box/i.test(userAgent)) {
        return 'settop';
    }

    // MediaPlayer
    if (/ipod|walkman/i.test(userAgent)) {
        return 'mediaplayer';
    }

    // Desktop and computers
    if (/pc|macintosh|mac os x|linux/i.test(userAgent)) {
        return 'computers';
    }

    // For dedicated TV screens (might overlap with others)
    if (/tv/i.test(userAgent)) {
        return 'tv screens';
    }

    // If none of the above, default to desktop (as a fallback)
    return 'desktop';
}

var deviceType = detectDeviceType();

function detectDeviceManufacturer() {
  var userAgent = navigator.userAgent || navigator.vendor || window.opera;

  // Windows Phone must come first because its UA also contains "Android"
  if (/windows phone/i.test(userAgent)) {
    return "Microsoft";
  }

  if (/android/i.test(userAgent)) {
    return "Android";
  }

  // iOS detection
  if (/iPad|iPhone|iPod/.test(userAgent) && !window.MSStream) {
    return "Apple";
  }

  return "unknown";
}

var deviceManufacturer = detectDeviceManufacturer();

var colorDepth = screen.colorDepth;

var screenWidth = window.screen.width;
var screenHeight = window.screen.height;

function getScreenOrientation() {
    if (window.screen && window.screen.orientation) {
        return window.screen.orientation.type;  // This will return values like 'portrait-primary', 'landscape-primary', etc.
    } else {
        // Fallback for older browsers
        if (window.innerWidth > window.innerHeight) {
            return 'landscape';
        } else {
            return 'portrait';
        }
    }
}

var screenOrientation = getScreenOrientation();

function detectOperatingSystem() {
  const userAgent = window.navigator.userAgent;
  const platform = window.navigator.platform;

  // Windows
  if (/Win(dows )?NT 10.0/.test(userAgent)) return 'Windows 10';
  if (/Win(dows )?NT 6.3/.test(userAgent)) return 'Windows 8.1';
  if (/Win(dows )?NT 6.2/.test(userAgent)) return 'Windows 8';
  if (/Win(dows )?NT 6.1/.test(userAgent)) return 'Windows 7';
  if (/Win(dows )?NT 6.0/.test(userAgent)) return 'Windows Vista';
  if (/Windows (NT 5.1|XP)/.test(userAgent)) return 'Windows XP';
  if (/Windows NT 5.0/.test(userAgent)) return 'Windows 2000';

  // Macintosh
  if (/Mac/.test(platform)) return 'MacOS';

  // iOS
  if (/iPhone/.test(userAgent) && !window.MSStream) return 'iOS iPhone';
  if (/iPad/.test(userAgent) && !window.MSStream) return 'iOS iPad';

  // Android
  if (/Android/.test(userAgent)) return 'Android';

  // Linux
  if (/Linux/.test(platform)) return 'Linux';

  // Other platforms
  if (/FreeBSD/.test(platform)) return 'FreeBSD';
  if (/OpenBSD/.test(platform)) return 'OpenBSD';
  if (/NetBSD/.test(platform)) return 'NetBSD';

  return 'Unknown';
}

var operatingSystem = detectOperatingSystem();

function detectCMS() {
    // Initial guess: Sitecore (as default)
    let cms = "Sitecore";

    // Check for WordPress
    if (document.querySelector('meta[name="generator"][content*="WordPress"]')) {
        cms = "WordPress";
    }
    // Check for Joomla
    else if (document.querySelector('meta[name="generator"][content*="Joomla"]')) {
        cms = "Joomla";
    }
    // Check for Drupal
    else if (window.Drupal || document.querySelector('meta[name="Generator"][content*="Drupal"]')) {
        cms = "Drupal";
    }
    // Check for Wix
    else if (document.querySelector('meta[http-equiv="X-Wix-Meta-Site-Id"]')) {
        cms = "Wix";
    }
    // Add other CMS checks as needed...

    return cms;
}

let cms = detectCMS();

function getBrowserDetails() {
    const userAgent = navigator.userAgent;
    let browserName, browserVersion, jsVersion;

    // Detect browser name and version
    if (/Opera[\/\s](\d+\.\d+)/.test(userAgent)) {
        browserName = 'Opera';
        browserVersion = RegExp.$1;
    } else if (/MSIE (\d+\.\d+);/.test(userAgent)) {
        browserName = 'Microsoft Internet Explorer';
        browserVersion = RegExp.$1;
    } else if (/Navigator[\/\s](\d+\.\d+)/.test(userAgent)) {
        browserName = 'Netscape';
        browserVersion = RegExp.$1;
    } else if (/Chrome[\/\s](\d+\.\d+)/.test(userAgent)) {
        browserName = 'Chrome';
        browserVersion = RegExp.$1;
    } else if (/Safari[\/\s](\d+\.\d+)/.test(userAgent)) {
        browserName = 'Safari';
        browserVersion = RegExp.$1;
    } else if (/Firefox[\/\s](\d+\.\d+)/.test(userAgent)) {
        browserName = 'Firefox';
        browserVersion = RegExp.$1;
    } else {
        browserName = 'Unknown';
        browserVersion = 'Unknown';
    }

    // JavaScript version
    jsVersion = (typeof Symbol === "function" && typeof Reflect === "object") ? 'ECMAScript 6 or above' : 'ECMAScript 5 or below';

    const acceptLanguage = navigator.language || navigator.userLanguage;
    const cookiesEnabled = navigator.cookieEnabled;
    const viewportHeight = window.innerHeight;
    const viewportWidth = window.innerWidth;
    const vendor = navigator.vendor;

    return {
        name: browserName,
        version: browserVersion,
        javaScriptVersion: jsVersion,
        userAgent: userAgent,
        acceptLanguage: acceptLanguage,
        cookiesEnabled: cookiesEnabled,
	    viewportHeight: viewportHeight,
        viewportWidth: viewportWidth,
        vendor: vendor
    };
}

const browserDetails = getBrowserDetails();

var pageURL = window.location.href;
var pageTitle = document.title;;
var pageQS = window.location.search.substring(1);
var siteName = window.location.hostname;

function removeQueryString(url) {
    let parsedUrl = new URL(url);
    return parsedUrl.protocol + "//" + parsedUrl.host + parsedUrl.pathname;
}

const pageURLWithoutQuery = removeQueryString(pageURL);

console.log("push device info");
window.adobeDataLayer.push({
    "device": {

        "colorDepth": colorDepth,
        "screenOrientation": screenOrientation,
        "screenHeight": screenHeight,
        "screenWidth": screenWidth,
        "type": deviceType,
        "typeIDService": "https://ns.adobe.com/xdm/external/deviceatlas",
        "manufacturer": deviceManufacturer

    }
});

// Retrieve the current page title and the previous page title from sessionStorage
let currentPageTitle = document.title;
let storedPreviousPageTitle = sessionStorage.getItem('previousPageTitle') || '';

// Initialize variable to hold the previous page title, if different
let differentPreviousTitle = null;

// Compare the two titles
if (currentPageTitle !== storedPreviousPageTitle) {
    differentPreviousTitle = storedPreviousPageTitle;
    sessionStorage.setItem('previousPageTitle', currentPageTitle);
}

window.adobeDataLayer.push({
    "environment": {
        "operatingSystem": operatingSystem,
        "browserDetails": browserDetails,
        "previousScreen": differentPreviousTitle,
        "viewedScreen": currentPageTitle
    }
});

let pageLanguage = document.documentElement.getAttribute('lang');

if (!pageLanguage || pageLanguage.trim() === '') {
    pageLanguage = 'en_us';
}

let pathArray = window.location.pathname.split('/').filter(part => part !== '');

// If there is a first directory, assign it to pageType. Else, assign 'home'.
let pageType = pathArray.length ? pathArray[0] : 'home';

//console.log(pageType);  // To verify the result
console.log("push 0-100 ");
    window.adobeDataLayer.push({
        "ucsfsitecontent": {
            "pagepercentageviewed": "0",
            "pagepercentageviewed25": "0",
            "pagepercentageviewed50": "0",
            "pagepercentageviewed75": "0",
            "pagepercentageviewed100": "0",
            "reviews": false
        }
    });

// SET DEFAULT VALUES FOR OPENSCHEDULING
window.adobeDataLayer.push({
    "openscheduling": {
        "demographicsnext": false,
        "groupnumber": false,
        "insurancepagesubmit": false,
        "loginschedule": false,
        "membernumber": false,
        "mychartsignin": false,
        "qualtricsfeedbackaccept": false,
        "scheduleguest": false,
        "start": false
    }
});

function getScrollPercentage() {
    // Current scroll position
    const scrollTop = window.scrollY || window.pageYOffset;

    // Viewport height
    const windowHeight = window.innerHeight;

    // Total height of the document - viewport height
    const scrollHeight = document.documentElement.scrollHeight - windowHeight;

    // Calculate the percentage
    const scrollPosition = scrollTop + windowHeight;
    const percentage = Math.floor((scrollPosition / document.documentElement.scrollHeight) * 100);

    return percentage;
}

// Example usage
window.addEventListener('scroll', function() {
    const percentageViewed = getScrollPercentage();
    window.adobeDataLayer.push({
        "event":"pagepercentageviewchange",
        "ucsfsitecontent": {
            "pagepercentageviewed": percentageViewed
        }
    });
    //console.log(percentageViewed);

    //if (percentageViewed >= 100) {

    //}
});

// BEGIN page:pageInfo:pageName

function getDomain() {
    var hostname = window.location.hostname;
    // Remove 'www.' if it exists
    var domain = hostname.startsWith('www.') ? hostname.substring(4) : hostname;
    // Split the domain by '.' and remove the last part (top-level domain)
    var parts = domain.split('.');
    parts.pop();
    // Join the remaining parts back together
    return parts.join('.');
}

function getPathWithoutDomain() {
    // Get the current URL
    const url = window.location.href;

    // Create a new URL object
    const parsedUrl = new URL(url);

    // Extract the pathname and remove the leading slash
    //let path = parsedUrl.pathname.substring(1);

    // Extract the pathname, remove the leading slash, and trim any trailing slashes
    let path = parsedUrl.pathname.substring(1).replace(/\/+$/, '');

    // Replace slashes with colons and convert to lowercase
    return path.replace(/\//g, ':').toLowerCase();
}

const server = getDomain();
const pagePath = getPathWithoutDomain();
const pageName = 'content:' + server + ':us:en:' + pagePath;
//const primaryCategory = "";

//END page:pageInfo:pageName

window.adobeDataLayer.push({
    "page": {
        "pageInfo": {
            "pageURL": pageURLWithoutQuery,
            "webLink": pageURL,
            "pageTitle": pageTitle,
            "pageName": pageName,
            "pageType": pageType,
            "contentType": "home (hardcoded)",
            "language": pageLanguage,
            "cms": cms,
            "server": "server (hardcoded)",
            "pageQS": pageQS,
            "siteName": siteName
        },
        "category": {
            "primaryCategory":"primaryCategory (hardcoded)",
            "subCategory1": "subCategory1 (hardcoded)",
            "subCategory2": "subCategory2 (hardcoded)"
	    },
        "elementvisible": ""
    }
});
//console.log ("primaryCat key created");
// BEGIN pageView

function generateUniqueId() {
  const timestamp = Date.now(); // current time in milliseconds
  const randomPortion = Math.random().toString(36).substr(2, 9); // generate a random alphanumeric string

  return `${timestamp}-${randomPortion}`;
}

const uniquePageViewId = generateUniqueId();

window.adobeDataLayer.push({
    "web": {
        "webPageDetails": {
            "pageViews": {
                "id": uniquePageViewId,
                "value": 1

            }
        }
    }
});




//END pageView
