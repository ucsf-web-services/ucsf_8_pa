(function(Drupal, once) {

  Drupal.behaviors.oneTrust = {
    attach: function(context) {
      const delayInMilliseconds = 1000; //1 second

      setTimeout(function() {
        // Hack to satisfy client requirements for this ticket:
        // https://ucsfredesign.atlassian.net/browse/UCSFD8-1372
        const oneTrustBannerTextLoaded= once('oneTrust', '#onetrust-banner-sdk', context).shift();
        if (!oneTrustBannerTextLoaded) {
          return;
        }
        const oneTrustBannerText = document.querySelector('#onetrust-policy-text');
        // for each link force to open in same tab
        const bannerLinks = oneTrustBannerText.querySelectorAll('a');
        bannerLinks.forEach(link => {
          link.setAttribute('target', '_self');
          link.removeAttribute('rel');
        });

        // One trust just adds aria-label to firs link it finds,
        // but in our usecase first link is not the privacy policy link
        // so we need to take that label and append it to the link where it belongs.
        const privacyAriaLabel = oneTrustBannerText.querySelector("[aria-label*='privacy']").ariaLabel.replace('opens in a new tab', 'opens in the same tab');
        const privacyLink = oneTrustBannerText.querySelector("[href*='privacy']");
        privacyLink.ariaLabel = privacyAriaLabel;
        // get the current aria label provided through OneTrust UI, replace the tab behavior text.
        oneTrustBannerText.querySelector("[href*='terms-of-use']").ariaLabel = "More information about UCSF terms of use, opens in the same tab";


      }, delayInMilliseconds);

    }
  }

})(Drupal, once);

