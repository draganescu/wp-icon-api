# Demo of WordPress Icon API

[See the discussion hosted by the Gutenberg repository](https://github.com/WordPress/gutenberg/discussions/53510)




https://github.com/draganescu/wp-icon-api/assets/107534/db30ea3f-fa21-441c-9347-920819d7a30b



### Icons!

Icons communicate condensed information and expectations of what happens after an action.  Given that the site editor is a design tool, for professionals and enthusiasts alike, there are countless situations that require a good icon: a call to action button that shows some love, a menu item that is special for 15 days, the visual highlight of a blog with well curated quotes, landing page sections that allow better conversions because icons make skimming easier, a quick logo solution, I could go on and on.

### Prior art

The 1st path explored was allowing users to upload SVG files which could then be used directly. That did not and still does not work. Starting with the long discussion in[ Reconsider SVG inclusion to get_allowed_mime_types](https://core.trac.wordpress.org/ticket/24251) and up to the recent attempt of a trimmed down option in Performance Lab plugin’s[ SVG uploads](https://github.com/WordPress/performance/issues/427)  letting normal author users simply uploading, previewing and inserting untrusted SVG content is hard to do in a secure manner. With thousands of security concerns and a format that is too flexible to properly sanitize, allowing low permission users uploading SVG as media is hard to do right.

So how about a 2nd path?

## Proposal

**Instead of allowing low permission users uploading SVG as media, we allow high permission users to install plugins which provide secure SVG media.**

SVG is complex, let’s restrict the discussion here only for using the format for icons.

As [suggested before](https://github.com/WordPress/gutenberg/issues/51563), I propose a system of registering SVG icons one by one in plugins. These plugins register icons just like they register blocks, with some function like:

```PHP
register_icon('my-plugin/book', {
	title: 'Book',
	icon: 'icons/book.svg', // or simply the SVG directly
	tags: 'book, lecture, paper, hardcover',
	category: 'decoration',
});
```

Or, even better, we could have a more general API like:


```PHP
register_vector('my-plugin/book', {
	type: ‘icon’,
	title: 'Book',
	icon: 'icons/book.svg', // or simply the SVG directly
	tags: 'book, lecture, paper, hardcover',
	category: 'decoration',
});
```

The second register method would allow vectors that are more than icons to be used in various ways: decoration, masking, featured content.

Either way, the goal is to make SVGs available from trusted sources.I 

Similarly to registering block collections we could also support registering icon collections, just for the same reasons, to visually group these icons where users can browse them.

All icons and collections registered should be available via the REST API in some `wp-json/wp/v2/icons` resource. The resource returns collections by default, supports searching for an icon and listing icons in collections and tags.

**In the block editor we introduce a new** `supports: icon,`. 

If a block supports using icons, a new control shows up in the design panel allowing a user to browse for icons and search for an icon. The block’s edit and save components can implement how they see fit the icon once the user chooses it, and Ideally they copy the SVG into markup or meta. The icon in the block inherits the default block styling (colors).

If we want to allow users to use standalone icons we can also add an icons block that only displays an icon and allows for styling it. If we implement this then the inserter could as well sport a nice icons tab searching through the same registered icon packs from plugins.

This option allows the ecosystem to provide curated and secure vector graphics, people with accurate permissions to curate these plugins before choosing them, and finally users to use vector graphics - albeit initially just for iconography for now - in their design and content. 

What do you think? Is this a terrible idea? Can it be built upon?
