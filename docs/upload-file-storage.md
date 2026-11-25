# Uploaded File Storage and Recovery

This document explains how questionnaire supporting files are stored, why some imported historical files may appear as missing locally, and how to recover them safely.

## Current Storage Behavior

New answer/supporting files are currently uploaded to:

```text
public/assets/answer-files/
```

For new uploads, `file_uploads.file_path` stores the generated filename only, not a full filesystem path.

The user and admin download routes resolve files by filename using `basename()` and search only the allowed answer-file locations:

```text
/mnt/iad/upload/answer-files/
storage/answer-files/
public/assets/answer-files/
```

The admin page uses the secure ID-based route:

```text
/admin/questionnaire/file?file_id=<id>
```

It does not use a public `/uploads` route and does not expose local filesystem paths.

## Why Some Files Show "Missing Local File"

Some file rows were inherited from a production SQL dump, but the matching physical upload files were not copied into this local environment.

In those cases:

- the database row still exists
- the file metadata still exists
- the physical file is not present on disk
- the admin UI shows `Missing local file`
- the secure download route returns `404`

This is expected for a database-only import. It is not a reason to delete the file row.

Recent local snapshot:

```text
Active file rows: 46
Reviewable file rows: 39
Reviewable files present locally: 3
Reviewable files missing locally: 36
```

These counts are only a local snapshot and may change as new files are uploaded or historical files are restored.

## Backup Requirements

A database-only backup is not enough for this application because uploaded-file rows depend on physical files on disk.

Back up these together:

- database dump
- `public/assets/answer-files/`
- any production mount equivalent to `/mnt/iad/upload/answer-files/`
- `storage/answer-files/` if the deployment uses it

Keeping the database and uploaded files together preserves the audit trail and allows old submissions to remain downloadable.

## Recovering Missing Historical Files

To restore missing files from production or another environment:

1. Obtain the production upload folder that contains questionnaire answer/supporting files.
2. Copy the matching files into an allowed local answer-file folder, preferably:

   ```text
   public/assets/answer-files/
   ```

3. Keep filenames unchanged.
4. Do not edit database rows manually unless filenames or stored paths truly changed.
5. Refresh the admin checking page.
6. Rows that now have matching physical files should automatically change from `Missing local file` to normal downloadable links.

If production stored files under a different root but with the same filename, copying those files into any allowed lookup folder is enough.

## Do Not Purge Without a Cleanup Plan

Do not delete or purge historical `file_uploads` rows just because the local physical file is missing.

Those rows may still be part of the audit/history record. Purging should only happen after a separate cleanup plan that includes:

- a backup
- a read-only audit of missing files
- confirmation that the rows are not needed
- explicit approval for soft-delete or archival behavior

## Future Improvement

A useful future enhancement would be a read-only admin maintenance report showing:

- total uploaded-file records
- files available on disk
- files missing locally
- missing files grouped by questionnaire, user, or date

That report should be read-only at first and should not include delete actions.
