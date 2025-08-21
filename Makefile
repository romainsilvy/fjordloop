SHELL := /bin/sh
.SHELLFLAGS := -eu -c
.ONESHELL:

.PHONY: changelog release clean

# Gitmoji
EMOJI_RELEASE   ?= :bookmark:
EMOJI_CHANGELOG ?= :memo:

# ------------------------------------------------------------
# 1) changelog : crée/actualise CHANGELOG.md
#    - crée le squelette "Unreleased" (sections préconfig)
#    - met à jour uniquement "### Commit history" sous Unreleased
#    - génère l'historique par tags si fichier absent
# ------------------------------------------------------------
changelog:
	CHANGELOG="CHANGELOG.md"

	# 1) Créer le fichier si absent (+ historique par tags)
	if [ ! -f "$$CHANGELOG" ]; then
	  {
	    echo "# Changelog"
	    echo
	    echo "All notable changes to this project will be documented in this file."
	    echo
	  } > "$$CHANGELOG"

	  TAGS=$$(git tag --list --sort=creatordate)
	  if [ -n "$$TAGS" ]; then
	    PREV=""
	    for CUR in $$TAGS; do
	      DATE=$$(git log -1 --format=%ad --date=short "$$CUR" 2>/dev/null || date +%F)
	      {
	        echo "## [$$CUR] - $$DATE"
	        echo
	        if [ -n "$$PREV" ]; then
	          git log --pretty=format:"- %s (%h)" --reverse --no-merges "$$PREV..$$CUR"
	        else
	          git log --pretty=format:"- %s (%h)" --reverse --no-merges "$$CUR"
	        fi
	        echo
	        echo
	      } >> "$$CHANGELOG"
	      PREV="$$CUR"
	    done
	  fi
	fi

	# 2) Commits non taggés (depuis le dernier tag)
	LATEST_TAG=$$(git describe --tags --abbrev=0 2>/dev/null || echo "")
	if [ -n "$$LATEST_TAG" ]; then
	  COMMITS=$$(git log --pretty=format:"- %s (%h)" --reverse --no-merges "$$LATEST_TAG..HEAD")
	else
	  COMMITS=$$(git log --pretty=format:"- %s (%h)" --reverse --no-merges)
	fi

	# 3) Préprépender Unreleased si absent, sinon MAJ du bloc "### Commit history"
	if ! grep -q '^## \[Unreleased\]' "$$CHANGELOG"; then
	  TMP=$$(mktemp)
	  {
	    echo "## [Unreleased]"
	    echo
	    echo "### Added";    echo "- "
	    echo
	    echo "### Changed";  echo "- "
	    echo
	    echo "### Fixed";    echo "- "
	    echo
	    echo "### Security"; echo "- "
	    echo
	    echo "### Docs";     echo "- "
	    echo
	    echo "### CI";       echo "- "
	    echo
	    echo "### Refactor"; echo "- "
	    echo
	    echo "### Perf";     echo "- "
	    echo
	    echo "### Chore";    echo "- "
	    echo
	    echo "### Commit history"
	    echo
	    if [ -n "$$COMMITS" ]; then printf "%s\n" "$$COMMITS"; else echo "- (no commits yet)"; fi
	    echo
	    echo
	    cat "$$CHANGELOG"
	  } > "$$TMP"
	  mv "$$TMP" "$$CHANGELOG"
	else
	  COMMITS_FILE=$$(mktemp)
	  printf "%s\n" "$$COMMITS" > "$$COMMITS_FILE"

	  awk -v cf="$$COMMITS_FILE" '
	    function dump_commits(cf,  line){ while((getline line < cf)>0) print line; close(cf) }
	    BEGIN{ in_unrel=0; in_hist=0 }
	    /^## \[Unreleased\]/{ in_unrel=1 }
	    {
	      if(in_unrel && $$0 ~ /^### Commit history/){ print $$0; print ""; dump_commits(cf); in_hist=1; next }
	      if(in_unrel && in_hist && ($$0 ~ /^### / || $$0 ~ /^## \[/)){ in_hist=0 }
	      if(in_hist){ next }
	      print $$0
	      if(in_unrel && $$0 ~ /^## \[/){ in_unrel=0 }
	    }' "$$CHANGELOG" > "$$CHANGELOG.tmp"

	  mv "$$CHANGELOG.tmp" "$$CHANGELOG"
	  rm -f "$$COMMITS_FILE"
	fi

	echo "CHANGELOG.md prêt. Remplis les sections d’Unreleased (Added/Changed/Fixed…)."

# ------------------------------------------------------------
# 2) release : publie depuis le CHANGELOG (gitmoji sur commits/tag)
#    - exige VERSION=X.Y.Z
#    - bump composer.json → commit "🔖 release: vX.Y.Z"
#    - tag annoté "vX.Y.Z" (message "🔖 Release vX.Y.Z")
#    - renomme Unreleased -> "[vX.Y.Z] - YYYY-MM-DD"
#    - commit "📝 changelog: cut vX.Y.Z"
#    - push & GitHub release (notes extraites du changelog)
# ------------------------------------------------------------
release:
	: "$${VERSION:?Error: VERSION=required (ex: make release VERSION=1.2.3)}"
	[ -f CHANGELOG.md ] && grep -q '^## \[Unreleased\]' CHANGELOG.md || { echo "Error: no 'Unreleased'. Run 'make changelog' et complète-le."; exit 1; }

	TAG="v$${VERSION}"
	DATE=$$(date +%F)

	echo "Bumping composer.json to $$VERSION..."
	if grep -q '"version"' composer.json; then
	  sed -i 's/"version": "[^"]*"/"version": "'$${VERSION}'"/' composer.json 2>/dev/null || \
	  sed -i '' 's/"version": "[^"]*"/"version": "'$${VERSION}'"/' composer.json 2>/dev/null || \
	  { tmp=$$(mktemp); sed 's/"version": "[^"]*"/"version": "'$${VERSION}'"/' composer.json > "$$tmp" && mv "$$tmp" composer.json; }
	else
	  sed -i 's/"name": "laravel\/livewire-starter-kit",/&\
    "version": "'$${VERSION}'",/' composer.json 2>/dev/null || \
	  sed -i '' 's/"name": "laravel\/livewire-starter-kit",/&\
    "version": "'$${VERSION}'",/' composer.json 2>/dev/null || \
	  { tmp=$$(mktemp); awk '1; $$0 ~ /"name": "laravel\/livewire-starter-kit",/ && !p {print "    \"version\": \""ENVIRON["VERSION"]"\","; p=1}' composer.json > "$$tmp" && mv "$$tmp" composer.json; }
	fi

	git add composer.json
	git commit -m "$(EMOJI_RELEASE) release: $$TAG" || true

	echo "Tagging $$TAG..."
	git tag -a "$$TAG" -m "$(EMOJI_RELEASE) Release $$TAG"

	echo "Freezing Unreleased -> $$TAG in CHANGELOG.md..."
	TMP=$$(mktemp)
	awk -v newhdr="## [$$TAG] - $$DATE" 'BEGIN{ replaced=0 } { if(!replaced && $$0 ~ /^## \[Unreleased\]/){ print newhdr; replaced=1; next } print }' CHANGELOG.md > "$$TMP"

	cat "$$TMP" >> CHANGELOG.md
	rm -f "$$TMP"

	git add CHANGELOG.md
	git commit -m "$(EMOJI_CHANGELOG) changelog: cut $$TAG" || true

	echo "Pushing branch and tag..."
	git push origin HEAD:main
	git push origin "$$TAG"

	echo "Publishing GitHub release from CHANGELOG section..."
	NOTES=$$(mktemp)
	awk -v tag="$$TAG" '
	BEGIN{
	  hit=0
	  header="## [" tag "] - "
	}
	{
	  if (hit==0) {
	    if (index($$0, header)==1) { hit=1; print; next }
	  } else {
	    if (substr($$0,1,4)=="## [") exit
	    print
	  }
	}
	' CHANGELOG.md > "$$NOTES"
	gh release create "$$TAG" --title "Release $$TAG" --notes-file "$$NOTES" --target main
	rm -f "$$NOTES"

	echo "✅ Release $$TAG published."

# ------------------------------------------------------------
# Nettoyage
# ------------------------------------------------------------
clean:
	rm -f CHANGELOG.md.tmp 2>/dev/null || true
	echo "Clean done."
