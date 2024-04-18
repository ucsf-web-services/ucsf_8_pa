
let topicsValues; // var for article tags
let authorValue; // var for author
let mediavalue; // var for article type
let articlevalue; // var for article area

if (dataLayer && dataLayer.length > 0 && dataLayer[0].entityTaxonomy && dataLayer[0].entityTaxonomy.topics && Object.keys(dataLayer[0].entityTaxonomy.topics).length > 0)
    {
     topicsValues = Object.values(dataLayer[0].entityTaxonomy.topics); // get values of 'tags' of the artcle
    } else {
         topicsValues = "";
    }
if (dataLayer && dataLayer.length > 0 && dataLayer[0].entityTaxonomy && dataLayer[0].entityTaxonomy.custom_authors && Object.keys(dataLayer[0].entityTaxonomy.custom_authors).length > 0)
    {
     authorValue = Object.values(dataLayer[0].entityTaxonomy.custom_authors); // get values of 'author' of the artcle

    } else {
         authorValue = "";
    }

if (dataLayer && dataLayer.length > 0 && dataLayer[0].entityTaxonomy && dataLayer[0].entityTaxonomy.article_type && Object.keys(dataLayer[0].entityTaxonomy.article_type).length > 0)
    {
     mediavalue = Object.values(dataLayer[0].entityTaxonomy.article_type); // get values of 'media type' of the artcle

    } else {
         mediavalue = "";
    }

if (dataLayer && dataLayer.length > 0 && dataLayer[0].entityTaxonomy && dataLayer[0].entityTaxonomy.areas && Object.keys(dataLayer[0].entityTaxonomy.areas).length > 0)
    {
     articlevalue = Object.values(dataLayer[0].entityTaxonomy.areas); // get values of 'area' of the artcle

    } else {
         articlevalue = "";
    }

window.adobeDataLayer.push({  //push to adobe's datalayer
    "eduSite": {
        "pageInfo": {
            "language": dataLayer[0].entityLangcode,
            "siteName": dataLayer[0].siteName,
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
