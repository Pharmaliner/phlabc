# Generate a presets.yaml from permission matrix

## Overview

This documentation describes the CLI command for generating a `presets.yaml` from a
previously exported role-permission matrix. The command reads a matrix file, derives
the effective permission set of each role, and writes a presets file that lists, per
role, the permissions it is granted.

It is the counterpart to the matrix export (see `Export.md`): export produces the
matrix, this command turns an (optionally reviewed and edited) matrix back into a
presets file.
This "round-trip" is what makes the matrix useful as a working document, not just
a report. A reviewer edits the permission model in the readable grid — flipping
a single cell to grant or revoke — instead of hand-editing wildcards and per-role
lists in the raw presets. Regenerating folds those changes back into the format
the application consumes, so the matrix is the thing you edit and presets.yaml
is a generated artifact, rather than two sources of truth to keep in sync.

---

## 1. Command

```bash
php vendor/bin/typo3 abc:generate:presetsFromPermissionsMatrix --input-file=<path>
```

The command parses the given matrix file into a permission matrix and generates the
corresponding presets from it. The input is a matrix file as produced by the export
command; the parser is selected from the file extension (`md`, `csv`).

---

## 2. Options

| Option          | Description                                      | Default                 |
|-----------------|--------------------------------------------------|-------------------------|
| `--input-file`  | Path to the permission matrix file to read.      | *(required)*            |
| `--output-path` | Path where the generated file will be written.   | `var/exports/presets.yaml` |

`--input-file` is required; the command will fail if this is empty. Missing directories in
the output path are created automatically.

---

## 3. Input

The input file is a role-permission matrix in one of the exported formats. The first
two columns are always `Permission-Key` and `Permission-Title`; every further column
is a role.

### 3.1 Markdown (`md`)

A table with `✅` (granted) / `❌` (not granted) cells:

```markdown
| Permission-Key | Permission-Title | role_admin | role_editor |
|---|---|---|---|
| perm_edit | Edit things | ✅ | ✅ |
| perm_view | View things | ✅ | ❌ |
```

### 3.2 CSV (`csv`)

An RFC 4180 compliant file with `yes` (granted) / `no` (not granted) cells:

```csv
Permission-Key,Permission-Title,role_admin,role_editor
perm_edit,Edit things,yes,yes
perm_view,View things,yes,no
```

Cell values are case-insensitive, and the common truthy/falsy spellings are accepted
on both formats (`✅`, `1`, `true`, `yes`, `y` for granted; `❌`, `0`, `false`, `no`,
`n`, or an empty cell for not granted). An empty title falls back to the permission
key.

### 3.3 Strictness

Parsing is strict: structural problems (a missing delimiter row, a wrong column
count, a blank or duplicate key, an unrecognised cell value) abort the command with
an error rather than producing a partial or silently wrong presets file. This is
intentional — the generated `presets.yaml` is meant to be inserted deliberately, not
patched around.

---

## 4. Output

The command writes a `presets.yaml` describing one preset per role:

```yaml
"presets":
  - "role": "role_admin"
    "permissions":
      - "*"
  - "role": "role_editor"
    "permissions":
      - "perm_edit"
      - "perm_view"
  - "role": "role_guest"
    "permissions": []
```

- **One entry per role**, in the column order of the matrix (roles are not sorted).
- **`permissions`** lists the keys of every permission the role is granted, in the
  matrix row order.
- **Wildcard collapse**: a role granted *every* permission is written as a single
  `"*"` instead of the full list.
- **Empty list**: a role granted no permissions is written as an empty sequence `[]`.

---

## 5. Typical Use Cases

1. **Round-trip** – Export the matrix, review or edit it, then regenerate
   `presets.yaml` from the edited matrix.
2. **Preset authoring** – Maintain the permission model in the readable matrix format
   and derive the machine-facing presets from it.
3. **Change review** – Regenerate after a matrix change and diff the resulting
   `presets.yaml` to verify the effect on each role.

---

## 6. Summary

The generate command provides:

1. **A single command** to derive `presets.yaml` from a permission matrix
2. **Multiple input formats** (`md`, `csv`) matching the export command
3. **Strict parsing** that fails fast on malformed input instead of emitting a wrong file
4. **Wildcard collapse** so a role with full access is written as `"*"`
