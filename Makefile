.PHONY: changelog changelog-preview release release-dry-run clean

# -------------------------------------------------------------------
# Changelog : reconstruit TOUT depuis les tags, sans duplication
# -------------------------------------------------------------------
changelog:
	@echo "Generating CHANGELOG.md from git tags..."
	@TMP_FILE=$$(mktemp); \
	echo "# Changelog" > $$TMP_FILE; \
	echo "" >> $$TMP_FILE; \
	echo "All notable changes to this project will be documented in this file." >> $$TMP_FILE; \
	echo "" >> $$TMP_FILE; \
	LATEST_TAG=$$(git describe --tags --abbrev=0 2>/dev/null || echo ""); \
	if [ -n "$$LATEST_TAG" ]; then \
		UNR=$$(git log --pretty=format:"- %s (%h)" --reverse --no-merges $$LATEST_TAG..HEAD); \
	else \
		UNR=$$(git log --pretty=format:"- %s (%h)" --reverse --no-merges); \
	fi; \
	if [ -n "$$UNR" ]; then \
		echo "## [Unreleased]" >> $$TMP_FILE; \
		echo "" >> $$TMP_FILE; \
		printf "%s\n" "$$UNR" >> $$TMP_FILE; \
		echo "" >> $$TMP_FILE; \
	fi; \
	TAGS=$$(git tag --list --sort=creatordate); \
	if [ -n "$$TAGS" ]; then \
		set -- $$TAGS; i=0; for t in $$TAGS; do eval "TAG$$i=$$t"; i=$$((i+1)); done; n=$$i; \
		for i in $$(seq $$((n-1)) -1 0); do \
			eval "CUR=\$$$$(printf TAG%d $$i)"; \
			if [ $$i -eq 0 ]; then PREV=""; else eval "PREV=\$$$$(printf TAG%d $$((i-1)))"; fi; \
			DATE=$$(git log -1 --format=%ad --date=short $$CUR 2>/dev/null || date +%F); \
			echo "## [$$CUR] - $$DATE" >> $$TMP_FILE; \
			echo "" >> $$TMP_FILE; \
			if [ -n "$$PREV" ]; then \
				git log --pretty=format:"- %s (%h)" --reverse --no-merges $$PREV..$$CUR >> $$TMP_FILE; \
			else \
				git log --pretty=format:"- %s (%h)" --reverse --no-merges $$CUR >> $$TMP_FILE; \
			fi; \
			echo "" >> $$TMP_FILE; \
			echo "" >> $$TMP_FILE; \
		done; \
	fi; \
	echo "Generated on $$(date '+%Y-%m-%d %H:%M:%S')" >> $$TMP_FILE; \
	mv $$TMP_FILE CHANGELOG.md; \
	echo "CHANGELOG.md generated successfully!"

# -------------------------------------------------------------------
# Aperçu du changelog (sans écrire le fichier)
# -------------------------------------------------------------------
changelog-preview:
	@$(MAKE) -s changelog
	@echo "=== CHANGELOG PREVIEW ==="
	@cat CHANGELOG.md

# -------------------------------------------------------------------
# Release : met à jour la version, tag, pousse, génère notes ciblées
# -------------------------------------------------------------------
release:
	@if [ -z "$(VERSION)" ]; then \
		echo "Error: VERSION parameter is required. Usage: make release VERSION=1.0.0"; \
		exit 1; \
	fi
	@echo "Creating release v$(VERSION)..."

	@echo "Updating composer.json version..."
	# Compat GNU/BSD sed : tente GNU puis BSD
	@sed -i 's/"version": "[^"]*"/"version": "$(VERSION)"/' composer.json 2>/dev/null || \
		sed -i '' 's/"version": "[^"]*"/"version": "$(VERSION)"/' composer.json 2>/dev/null || \
		sed -i 's/"name": "laravel\/livewire-starter-kit",/&\
    "version": "$(VERSION)",/' composer.json 2>/dev/null || \
		sed -i '' 's/"name": "laravel\/livewire-starter-kit",/&\
    "version": "$(VERSION)",/' composer.json
	@echo "Version updated to $(VERSION) in composer.json"

	@echo "Committing version bump..."
	@git add composer.json
	@git commit -m "chore: bump version to v$(VERSION)" || true

	@echo "Creating git tag v$(VERSION)..."
	@git tag -a "v$(VERSION)" -m "Release v$(VERSION)"

	@echo "Regenerating CHANGELOG.md..."
	@$(MAKE) -s changelog
	@git add CHANGELOG.md
	@git commit -m "docs(changelog): update for v$(VERSION)" || true

	@echo "Pushing changes and tag..."
	@git push origin HEAD:main
	@git push origin "v$(VERSION)"

	@echo "Preparing release notes for v$(VERSION)..."
	@awk 'BEGIN{p=0} index($$0,"## [v$(VERSION)]")==1{p=1; print; next} /^## \[/{if(p){exit}} p{print}' CHANGELOG.md > RELEASE_NOTES.md


	@echo "Creating GitHub release..."
	@gh release create "v$(VERSION)" --title "Release v$(VERSION)" --notes-file RELEASE_NOTES.md --target main
	@echo "Release v$(VERSION) created successfully on GitHub!"
	@echo "GitHub release URL: https://github.com/$(shell git config --get remote.origin.url | sed 's/.*github.com[:/]\([^/]*\/[^/]*\).*/\1/' | sed 's/\.git//')/releases/tag/v$(VERSION)"

# -------------------------------------------------------------------
# Dry run : ne pousse ni ne crée la release
# -------------------------------------------------------------------
release-dry-run:
	@if [ -z "$(VERSION)" ]; then \
		echo "Error: VERSION parameter is required. Usage: make release-dry-run VERSION=1.0.0"; \
		exit 1; \
	fi
	@echo "=== DRY RUN: Testing release v$(VERSION) ==="
	@echo "Would update composer.json version to $(VERSION)"
	@echo "Would create tag v$(VERSION)"
	@echo "Would regenerate CHANGELOG.md from tags (no duplicates)"
	@echo "Would extract notes for v$(VERSION) to RELEASE_NOTES.md"
	@echo "Would NOT push to GitHub (dry run)"
	@echo "Would NOT create GitHub release (dry run)"
	@echo "=== DRY RUN COMPLETE ==="

# -------------------------------------------------------------------
# Nettoyage
# -------------------------------------------------------------------
clean:
	@rm -f CHANGELOG.md RELEASE_NOTES.md
	@echo "Cleaned generated files"
