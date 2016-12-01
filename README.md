# Anime Download script #

With this PHP script you can easily download any anime

## Setup ##
Clone this repository in your desktop
- Fireup http://kissanime.to in your browser
- Then open the networks tab from console and copy the "Cookie" and "User agent" header
- Edit the KissAnime/config.json

And run this simple line in your terminal

```bash
./init "http://kissanime.to/Anime/Fairy-Tail-Dub" "http://kissanime.to/Anime/Fairy-Tail-Dub/001-Fairy-Tail?id=116233"
```

Explaination
```bash
./init "{Anime Episode URL}" "{Anime starting episode URL} (Optional)" "{Anime ending episode URL} (Optional)"
```

After this command has completed there will be a new file "list.txt" in this directory containing the links of the episodes which can be passed to any download manager

MAC and Linux/Unix Users can simple run this command by opening this directory in terminal. This will download the videos in a "downloads" folder serial wise
```bash
./axelLinks
```