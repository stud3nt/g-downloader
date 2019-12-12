export class ParserNode {

	constructor(obj?: any) {
		this.statuses = [];

		Object.assign(this, obj);
	}

	id: number;

	name: string;

	description: string = null;

	level: string = null;

	nextLevel: string = null;

	parser: string = null;

	identifier: string = null;

	url: string = ' ';

	ratio: number = 0;

	noImage: boolean = false

	singleFile: boolean = false;

	imagesNo: number = 0;

	commentsNo: number = 0;

	thumbnails: Array<string> = [];

	localThumbnails: Array<string> = [];

	statuses: Array<any> = [];

	settings: object = {};

	// statuses
	queued: boolean = false;
	favorited: boolean = false;
	finished: boolean = false;
	blocked: boolean = false;

	/**
	 * Adds status to library
	 *
	 * @param addedStatus
	 */
	public addStatus(addedStatus: string): ParserNode {
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
	public removeStatus(removedStatus: string): ParserNode {
		if (this.statuses.length > 0) {
			for (let statusKey in this.statuses) {
				if (this.statuses[statusKey] === removedStatus) {
					delete this.statuses[statusKey];
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
		if (this.statuses.length > 0) {
			for (let statusKey in this.statuses) {
				if (this.statuses[statusKey] === checkedStatus) {
					return true;
				}
			}
		}

		return false;
	}
}