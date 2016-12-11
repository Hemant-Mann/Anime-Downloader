# Anime Download script #

With this PHP script you can easily download any anime

## Setup ##
Clone this repository in your desktop
- Fireup http://kissanime.to in your browser
- Then open the networks tab from console and copy the "Cookie" and "User agent" header
- Edit the KissAnime/config.json (If not present then copy and rename the sample-config.json)

And run this simple line in your terminal

```bash
./init "http://kissanime.io/Anime/fairy-tail-dub" "http://kissanime.io/Anime/fairy-tail-dub/watch.html?episode_id=6000" "http://kissanime.io/Anime/fairy-tail-dub/watch.html?episode_id=6090"
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