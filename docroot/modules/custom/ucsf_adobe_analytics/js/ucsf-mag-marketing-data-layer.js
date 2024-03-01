
let topicsValues; // var for article tags
let authorValue;
let mediavalue;
let articlevalue;

if (dataLayer && dataLayer.length > 0 && dataLayer[0].entityTaxonomy && dataLayer[0].entityTaxonomy.topics && Object.keys(dataLayer[0].entityTaxonomy.topics).length > 0)
    {
     topicsValues = Object.values(dataLayer[0].entityTaxonomy.topics); // get values of 'tags' of the artcle
    } else {
         topicsValues = "not set";
    }
if (dataLayer && dataLayer.length > 0 && dataLayer[0].entityTaxonomy && dataLayer[0].entityTaxonomy.custom_authors && Object.keys(dataLayer[0].entityTaxonomy.custom_authors).length > 0)
    {
     authorValue = Object.values(dataLayer[0].entityTaxonomy.custom_authors); // get values of 'author' of the artcle

    } else {
         authorValue = "not set";
    }

if (dataLayer && dataLayer.length > 0 && dataLayer[0].entityTaxonomy && dataLayer[0].entityTaxonomy.article_type && Object.keys(dataLayer[0].entityTaxonomy.article_type).length > 0)
    {
     mediavalue = Object.values(dataLayer[0].entityTaxonomy.article_type); // get values of 'media type' of the artcle

    } else {
         mediavalue = "not set";
    }

if (dataLayer && dataLayer.length > 0 && dataLayer[0].entityTaxonomy && dataLayer[0].entityTaxonomy.areas && Object.keys(dataLayer[0].entityTaxonomy.areas).length > 0)
    {
     articlevalue = Object.values(dataLayer[0].entityTaxonomy.areas); // get values of 'area' of the artcle

    } else {
         articlevalue = "not set";
    }



window.adobeDataLayer.push({  //push to adobe's datalayer
    "eduSite": {
        "pageInfo": {
            "language": dataLayer[0].entityLangcode,  //take this out
            "siteName": dataLayer[0].siteName,  //take this out
            "pageType": dataLayer[0].entityBundle,


        },
        "category": {
            "primaryCategory": topicsValues,
            "author": authorValue,
            "mediatype": mediavalue,
            "articelArea": articlevalue,
          //  "organization":,

        },

    }
});
