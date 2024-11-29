# Assets

**Core Framework Assets**

## Hanlding


### Core
Located in `.framework/assets/styles/*.css`, these serve as the baseline styles.

All generated stylesheets will use these.

The application styles in `~app/assets/styles/*.css` will be merged,
overriding each file. To extend a given file, use `dot.notation.css`.

### Admin
Admin specific styles.

### Public
Public baseline.

The application is intended to add its own styles, either through `tailwind`, the `editor`, or using the `DesignSystemService`.
