
console.log("run mag DL")
/*
window.adobeDataLayer = window.adobeDataLayer || [];

const topicsValues = Object.values(dataLayer[0].entityTaxonomy.topics);

console.log(topicsValues);



window.adobeDataLayer.push({
		"page": {
			"pageInfo": {
			"language":dataLayer[0].entityLangcode,
        "siteName":dataLayer[0].siteName,
        "pageType":dataLayer[0].entityBundle,
      //  "elementvisible":topicsValues,
      //  "elementvisible":Object.values(dataLayer[0].entityTaxonomy.topics)
		  },
	}
});
*/

/*
//test 2 the below will add the topicsValues array but to another page group


window.adobeDataLayer.push({
		"page": {
			"pageInfo": {
			"language":dataLayer[0].entityLangcode,
        "siteName":dataLayer[0].siteName,
        "pageType":dataLayer[0].entityBundle,
        //"pageQS":topicsValues,
      //  "elementvisible":Object.values(dataLayer[0].entityTaxonomy.topics)
		  },
      "category": {
        "primaryCategory":topicsValues,
      },
    //  "elementvisible":"test",
	}
});

*/




/*
const dataToPush = {
  "page":{
    "pageInfo":{
      "elementvisible": topicsValues
    },

  }

};
window.adobeDataLayer.push(dataToPush);
*/

//window.digitalData = JSON.parse('<%= JSON.stringify(adobeDataLayer, null, 2) %>');
/* code to try to print to html the js object
var dataLayer = {
  "entityTaxonomy": {
    "custom_authors": {
      // Your pageInfo properties here
    },
    // Additional properties here
  },
  // Other top-level properties here
};

var scriptContent = `window.digitalData = ${JSON.stringify(dataLayer, null, 2)};`;

// Create a script element
var scriptEl = document.createElement('script');
scriptEl.type = 'text/javascript';
// Set the script content, properly escaping it if necessary
scriptEl.textContent = scriptContent;

// Append the script element to the head or body of the document
document.head.appendChild(scriptEl);
*/


//adobe load
