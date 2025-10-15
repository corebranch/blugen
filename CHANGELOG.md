# Changelog

All notable changes to this project will be documented in this file.

The format is based on Keep a Changelog and this project adheres to Semantic Versioning.

## [1.0.0-alpha.2] - 2025-10-15

### Breaking
- SchemaInterface now requires `toArray(): array`.
  - Any custom implementations of `Blugen\\Service\\Lexicon\\SchemaInterface` must add `toArray()`.
- Namespace and layout refactor for type-specific schemas.
  - Old: `Blugen\\Service\\Lexicon\\V1\\TypeSpecificSchema\\...`
  - New roots under `Blugen\\Service\\Lexicon\\V1\\Schema\\...` with sub-namespaces:
    - `Concrete\\*` (e.g., `StringSchema`, `IntegerSchema`, `BlobSchema`, ...)
    - `Container\\*` (e.g., `ArraySchema`, `ObjectSchema`, `ParamsSchema`)
    - `Meta\\*` (e.g., `RefSchema`, `UnionSchema`, `UnknownSchema`, `TokenSchema`)
    - `Support\\*` (e.g., `InputSchema`, `OutputSchema`, `MessageSchema`, `ErrorsSchema`, `ErrorSchema`)
    - `Primary\\*` (e.g., `QuerySchema`, `ProcedureSchema`, `RecordSchema`, `SubscriptionSchema`, `HTTPAbstract`)
  - Code importing old `TypeSpecificSchema` classes will break and must be updated to the new `Schema` sub-namespaces.
- Support schema `type()`/`description()` behavior changed via `SupportSchemaTrait`.
  - `type()` is no longer supported on support schemas and now throws `BadMethodCallException`.
  - `description()` on some support schemas may also be unsupported (unless class overrides it).
- `InputSchema::schema()` / `OutputSchema::schema()` return raw arrays.
  - In 1.x these methods returned wrapped schema objects (e.g., `ObjectSchema|UnionSchema|RefSchema|null`).
  - Now they return `?array`/`array` of raw schema content. Callers must adapt accordingly.
  - `InputSchema::schema()` may now be `null` when absent (previously defaulted to an empty object schema).
- `ErrorsSchema` element type and access semantics changed.
  - Iteration now yields `ErrorSchema` objects (previously arrays).
  - Implements read-only `ArrayAccess`; attempting to modify throws `BadMethodCallException`. Out-of-bounds reads throw `OutOfBoundsException`.
- `ObjectSchema::properties()` contract changed.
  - Returns an array of schema instances built via `SchemaFactory` (previously returned `Property` objects with name/nullable/required metadata).
  - Throws `V1\\Exceptions\\MissingRequiredFieldException` when `properties` is missing or not an array (was previously tolerant).
  - Callers needing metadata must use `required()`/`nullable()` arrays alongside property keys.
- Legacy classes removed:
  - `V1/TypeSpecificSchema/Support/{ErrorsSchema,InputSchema,MessageSchema,OutputSchema}`
  - `V1/TypeSpecificSchema/Field/ObjectSchema`

### Added
- Primary schemas: `Schema\\Primary\\{QuerySchema, ProcedureSchema, RecordSchema, SubscriptionSchema}` and base `HTTPAbstract`.
- Traits to unify behavior and DRY:
  - `V1\\Traits\\{ArrayableTrait, SchemaTrait, RawSchemaAccessorTrait, SupportSchemaTrait, DefinitionTrait}`
- `V1\\Schema\\Support\\ErrorSchema` and revamped `ErrorsSchema` (iterable, read-only `ArrayAccess`).
- `V1\\Exceptions\\MissingRequiredFieldException` for strict schema validation (e.g., object properties presence/type).

### Changed
- Moved all type-specific schemas to `V1\\Schema\\{Concrete, Container, Meta, Support, Primary}` per their roles.
- `SchemaFactory` now creates from new `V1\\Schema\\{Concrete,Container,Meta}` classes; dependent generators updated accordingly.
- IO support schemas share a common `IOAbstract` (unified `description()`, `encoding()`, `schema()`, `parameters()`, `toArray()`).
- `OutputSchema`: `schema` field is optional and may be `null` when not present.

### Fixed
- Treat `schema` as optional for Input/Output; tests and implementations updated accordingly.
- Resolved issues in `ObjectSchema` behavior and offset/exception handling in support schemas.

### Removed
- Old `TypeSpecificSchema` support classes and the field `ObjectSchema` (replaced by `Schema\\Container\\ObjectSchema`).

### Migration Guide
- Implementations of `SchemaInterface`: add `public function toArray(): array`.
- Update imports from `...\\V1\\TypeSpecificSchema\\Field\\*` to the new locations. Examples:
  - `...\\TypeSpecificSchema\\Field\\StringSchema` → `...\\Schema\\Concrete\\StringSchema`
  - `...\\TypeSpecificSchema\\Field\\ObjectSchema` → `...\\Schema\\Container\\ObjectSchema`
  - `...\\TypeSpecificSchema\\Field\\RefSchema` → `...\\Schema\\Meta\\RefSchema`
  - `...\\TypeSpecificSchema\\Support\\InputSchema` → `...\\Schema\\Support\\InputSchema`
- Support schema `type()` calls: remove/avoid calling `type()` on support schemas or guard with `method_exists`/try-catch.
- Input/Output/Message `schema()` usage:
  - Previously: `$schemaObj = $output->schema(); $type = $schemaObj?->type();`
  - Now: `$raw = $output->schema(); $type = $raw['type'] ?? null;` or wrap via `new V1\\Schema($raw)` and/or `SchemaFactory::create($raw['type'], $raw)`.
- `ErrorsSchema` iteration and access:
  - Previously arrays: `$name = $errors[0]['name'];`
  - Now objects: `$name = $errors[0]->name(); $desc = $errors[0]->description();`
  - Collection is read-only; do not modify via array offsets.
- `ObjectSchema::properties()`:
  - Previously returned `Property` objects with `name/nullable/required`.
  - Now returns schema instances only. Use `required()`/`nullable()` and your properties keys to compute metadata.
  - Handle `MissingRequiredFieldException` whenever reading properties of an object schema.

## [1.0.0-alpha.1] - 2025-07-26

### Added
- First 1.x pre-release tag for the library.
- CLI `blugen` with `generate` and `clear` commands.
- Lexicon V1 foundation: reader, generator, definition and schema interfaces.
- Type-specific schemas under `V1\TypeSpecificSchema\{Field,Support}` and supporting `SchemaFactory`.
- Basic syntax validators for string, integer, boolean, array, and null types.
- XRPC client pieces: `Client`, `SessionManager`, encoders and helpers.

### Changed
- Release automation and workflow adjustments for publishing the 1.x pre-release.

---
[1.0.0-alpha.2]: https://github.com/corebranch/blugen/compare/1.0.0-alpha.1..1.0.0-alpha.2
[1.0.0-alpha.1]: https://github.com/corebranch/blugen/releases/tag/1.0.0-alpha.1
