<?php
// Versão do sistema e histórico de mudanças (mostrado no selo de versão do menu).
const APP_VERSION = '1.1.1';

const CHANGELOG = [
    ['versao' => '1.1.1', 'data' => '2026-09-30', 'itens' => [
        'Ajustes para celular e tablet: listas viram cartões (sem rolagem lateral), formulários em tela cheia, botões maiores e painel reorganizado.',
        'Pode ser adicionado à tela inicial do celular (ícone e cor da barra do navegador).',
    ]],
    ['versao' => '1.1.0', 'data' => '2026-09-30', 'itens' => [
        'Nova tela de Relatórios: evolução mês a mês, gastos por categoria e maiores gastos.',
        'Exportação do histórico em CSV e em PDF (via impressão do navegador).',
        'Recuperação de senha por e-mail, com link de uso único válido por 1 hora.',
        'Tema claro, escuro ou automático (segue o sistema).',
        'Tela de instalação: configura o banco de dados no primeiro acesso.',
        'Visual repaginado e textos revisados.',
    ]],
    ['versao' => '1.0.0', 'data' => '2026-09-30', 'itens' => [
        'Cadastro e login de usuários.',
        'Entradas, gastos, histórico com filtros, edição e exclusão.',
        'Painel com totais, gráfico por categoria, alertas, dica do dia e conquistas.',
        'Metas de economia com progresso.',
    ]],
];
