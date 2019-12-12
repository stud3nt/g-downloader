export class ParsedFile {

	constructor(obj?: any) {
		Object.assign(this, obj);

		if (!obj.statuses || obj.statuses.length === 0) {
			this.statuses = [];
		}
	}

	public name: string;

	public title: string = null;

	public description: string = null;

	public extension: string;

	public parser: string;

	public identifier: string;

	public thumbnail: string = null;

	public localThumbnail: string = null;

	public url: string = null;

	public localUrl: string = null;

	public width: number = 0;

	public height: number = 0;

	public length: number = 0;

	public size: number = 0;

	public textSize: string;

	public uploadedAt: string;

	public htmlPreview: string = null;

	// file type (image, movie)
	public type: string;

	// specific file settings (renamer, folder etc);
	public settings: object = {};

	// statuses library
	public statuses = [];

	/**
	 * Adds status to library
	 *
	 * @param addedStatus
	 */
	public addStatus(addedStatus: string): ParsedFile {
		if (!this.hasStatus(addedStatus)) {
			this.statuses.push(addedStatus);
		}

		return this;
	}

	/**
	 * Removes status from library
	 *
	 * @param removedStatus
	 */
	public removeStatus(removedStatus: string): ParsedFile {
		if (this.statuses && this.statuses.length > 0) {
			for (let statusKey in this.statuses) {
				let intStatusKey = parseInt(statusKey);

				if (this.statuses[statusKey] === removedStatus) {
					this.statuses.splice(intStatusKey, 1);
				}
			}
		}

		return this;
	}

	/**
	 * Checks if status exists in library;
	 *
	 * @param checkedStatus
	 */
	public hasStatus(checkedStatus: string): boolean {
		if (this.statuses && this.statuses.length > 0) {
			for (let statusKey in this.statuses) {
				if (this.statuses[statusKey] === checkedStatus) {
					return true;
				}
			}
		}

		return false;
	}
}