# Website

All pages are available at [sparrowcode.io/en](https://sparrowcode.io/en) & [sparrowcode.io/ru](https://sparrowcode.io).

## Contribute

- [Tutorials](#tutorials)
    - [Content](#content)
    - [Formatting](#formatting)
    - [Meta](#meta)
- [Apps](#apps)

## Tutorials

Choose the language in which you want to write. Then your tutorial may be translated into another language with an indication of the author. Now available in Russian `ru` and English `en`.
Create a file with the name of the path where the page will be accessible, for example, a new file [/en/tutorials/edge-insets-uibutton.md](/en/tutorials/edge-insets-uibutton.md).

### Content

You can set text, pictures, and video. I offer my hosting, but you can use any other. Try not to use large videos - users don't like long loading times. If you want to use my hosting, simply send me an archive with files and the path of the tutorial - I will add it shortly.

### Formatting

Basic markdown functions are supported, like title, subtitle, and paragraph. Also available are links, images, and video. Here provided list:

Titles

```
## Title
### Subtitle
```

Link

```
[Link Name](url)
```

Formatting

```
// Bold Text
**Example**

// Higlight quote in orange area  
>Some important info.
```

Image and Video
 
```
![Image Description](https://myoctocat.com/assets/images/base-octocat.svg)
[Video Description](https://cdn.sparrowcode.io/tutorials/drag-and-drop-part-1/drag-delegate.mov)
```

For highlight link to the grey area with title and subtitle, use this custom formatting:

```
[title](url): description
```
Example [here](https://sparrowcode.io/resources-for-ios-developer).

### Meta

Fill in the details of the article for file [/en/meta/articles.json](/en/meta/articles.json). If the article already exists, set the date of the last change and indicate yourself as editor or translator. All fields are listed here, some of them are optional.

- `title` - Title of your tutorial.
- `description` - Description of tutorial.
- `category` - Category ID, read next section.
- `author` - Author ID, read next section.
- `keywords` - Array of relative keys for your article.
- `updated_date` - Date of last updating article. Format `01.01.2022`.
- `added_date` - Date of created article. Format `01.01.2022`.

#### Optional

- `editors` - An array of author IDs. If you fix some typos, add your username here. 
- `translator` - Author ID.

List of categories available at [/en/meta/categories.json](/en/meta/categories.json). If you need an additional category, add it. Make sure none of the existing ones fit.

Authors available at [/en/meta/authors.json](/en/meta/authors.json). Fill in short information about yourself, you can add buttons to the GitHub or your page in the App Store.

## Apps

Choose the language in which you want to write. If you want add app to `en`, navigate to file [en/meta/apps.json](en/meta/apps.json). If your app supported `en` and `ru`, make changes for both files.

Fill with example data: 

```json
{
  "developer_name" : "Ivan Vorobei",
  "github_username" : "ivanvorobei",
  "apps" : [
    {
      "id" : "1570676244",
      "name" : "Debts - Debt Tracker",
      "added_date" : "06.02.2022"
    }
  ]
}
```

And open Pull Request after.
