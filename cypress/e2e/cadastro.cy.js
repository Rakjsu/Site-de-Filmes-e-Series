describe('Cadastro de Usuário', () => {
  it('deve cadastrar um novo usuário', () => {
    const nome = 'teste' + Date.now();
    const email = `${nome}@exemplo.com`;
    cy.cadastrarUsuario(nome, email, '123456');
    cy.contains('Cadastro realizado').should('be.visible');
  });
}); 