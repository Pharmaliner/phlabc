# Exporting the Role-Permission Matrix

## Overview

This documentation describes the CLI command for exporting the role-permission matrix as a file. The command aggregates permissions, roles, and presets from all installed extensions and produces a matrix showing which role is granted which permission.

---

## 1. Command

```bash
php vendor/bin/typo3 abc:export:permissionsmatrix
```

The command reads the YAML files defined in the `composer.json` of each installed extension (see the integration documentation) and builds a matrix mapping every known permission to every known role.

---

## 2. Options

| Option            | Description                                    | Default                          |
|-------------------|------------------------------------------------|----------------------------------|
| `--output-path`   | Path where the output file will be written.    | `var/exports/permissions-matrix` |
| `--format`        | Output format. Available: `md`, `csv`.         | `md`                             |

The file extension of `--output-path` is automatically adjusted to match the selected format. Missing directories in the path are created automatically.

---

## 3. Output Formats

### 3.1 Markdown (`md`)

Renders the matrix as a markdown table with `✅` and `❌` cells. Suitable for documentation, wikis, and pull request descriptions.

```bash
php vendor/bin/typo3 abc:export:permissionsmatrix --format=md
```

### 3.2 CSV (`csv`)

Renders the matrix as an RFC 4180 compliant CSV file with `yes` and `no` cells. Suitable for spreadsheet analysis and further processing.

```bash
php vendor/bin/typo3 abc:export:permissionsmatrix --format=csv
```

---

## 4. Permission matrix Structure

- **Rows**: All permissions defined across all extensions, sorted alphabetically by `permission_key`.
- **Columns**: All roles defined across all extensions, in first-seen order.
- **Cells**: Indicate whether the role in the column is granted the permission in the row, based on the preset patterns assigned to that role.

Wildcard patterns in presets (`*` and prefix patterns such as `example-*`) are resolved during matrix generation, so a role with `"*"` in its preset is marked as granted for every permission.

---

## 5. Typical Use Cases

1. **Documentation** – Commit the exported markdown file to your repository to keep an up-to-date overview of the permission model.
2. **Audits** – Export as CSV and review role assignments in a spreadsheet.
3. **Change review** – Regenerate the matrix after changes to any YAML file and compare the diff to verify the impact of the change.

---

## 6. Summary

The export command provides:

1. **A single command** to visualize the complete role-permission matrix
2. **Multiple output formats** for different use cases (documentation vs. analysis)
3. **Automatic aggregation** across all installed extensions
4. **Wildcard resolution** so the matrix reflects the effective permissions of each role
