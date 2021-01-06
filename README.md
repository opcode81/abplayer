# A/B Player

**A web-based audio player for A/B sound comparisons**

A/B player provides a web interface to audition mp3 files contained in a directory and will present all the files contained in the directory in a simple interface. Users can (almost) seamlessly switch between the files and directly compare them. Sound files are presented in lexicographical order and a letter is assigned to each sound file, such that they can more easily be referenced in discussions.

[Click here](http://www.power-xs.de/opcode/abplayer/index.php?d=at-cabs-c90) for a demonstration.

## Installation and Usage

1. Deploy `abplayer` on a web server with PHP support, e.g. `http://myserver/abplayer`
2. For every A/B comparison you want an A/B player for, create a sub-directory in `tracks ` containing a set of MP3 files.
3. Navigate to `http://myserver/abplayer/index.php?d=my-comparison` for an A/B player with the files in `tracks/my-comparison`

## Options

Additional GET parameters which can be used to control behaviour:

* `restart`: when set to a truthy value (e.g. 1), playback will restart from the beginning when switching to a new file.