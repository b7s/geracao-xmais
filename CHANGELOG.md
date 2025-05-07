# Changelog

## [Unreleased]

### Added
- Nova coluna `membro_desde` na tabela de associados para registrar a data de associação
- Campo para edição da data de associação no formulário de cadastro/edição
- Suporte à coluna `MEMBRO DESDE (DATA)` na importação de associados via CSV
- Comando `associados:update-membros-desde` para atualizar registros existentes

### Changed
- Atualização do perfil do associado para mostrar a data correta de associação ao invés de created_at
- Atualização do modelo de importação CSV para incluir a nova coluna 