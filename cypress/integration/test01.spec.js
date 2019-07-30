describe('Homepage', function() {

  it('visits homepage', function() {
    cy.visit('/');
    cy.get('.field-banner-title')
      .contains('Advancing Health for Everyone, Everywhere');
  });


});
/*
describe('Login', function() {
  it('logs in via ui', function(){
    cy.visit('/user/login');
    cy.get('#edit-name').type(Cypress.env('cyAdminUser'));
    cy.get('#edit-pass').type(Cypress.env('cyAdminPassword'));
    cy.get('#edit-submit').click();
  });

});
*/