import nexus

# Delete all 0.0 versions of com.mode.quack-fire.* components

repo = nexus.Repo("releases")

for project in repo.get("/com/mode/quack-fire/"):
    print project
    if isinstance(project, nexus.Folder):
        for sub in project:
            print "\t",sub, sub.filename
            if sub.filename.startswith("0.0."):
                sub.delete()
