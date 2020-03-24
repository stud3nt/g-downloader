import { BaseModel } from "./base/base-model";
import { Category } from "./category";
import { Tag } from "./tag";

export class ParserNode extends BaseModel {

	constructor(obj?: any) {
		super();

		this._statuses = [];

		Object.assign(this, obj);
	}

	private _id: number;

	private _name: string;

	private _label: string;

	private _rating: number = 0;

	private _description: string = null;

	private _customDescription: string = null;

	private _level: string = null;

	private _nextLevel: string = null;

	private _parser: string = null;

	private _identifier: string = null;

	private _url: string = null;

	private _ratio: number = 0;

	private _noImage: boolean = false;

	private _singleFile: boolean = false;

	private _imagesNo: number = 0;

	private _commentsNo: number = 0;

	private _thumbnails: Array<string> = [];

	private _localThumbnails: Array<string> = [];

	private _statuses: Array<any> = [];

	private _settings: object = {};

	private _lastViewedAt: string = '---';

	private _allowTags: boolean = false;

	private _allowCategory: boolean = false;

	private _category: Category = null;

	private _tags: Tag[] = [];

	// statuses
	private _queued: boolean = false;
	private _favorited: boolean = false;
	private _finished: boolean = false;
	private _blocked: boolean = false;

	get id(): number {
		return this._id;
	}

	set id(value: number) {
		this._id = value;
	}

	get name(): string {
		return this._name;
	}

	set name(value: string) {
		this._name = value;
	}

	get label(): string {
		return this._label;
	}

	set label(value: string) {
		this._label = value;
	}

	get rating(): number {
		return this._rating;
	}

	set rating(value: number) {
		this._rating = value;
	}

	get description(): string {
		return this._description;
	}

	set description(value: string) {
		this._description = value;
	}

	get level(): string {
		return this._level;
	}

	set level(value: string) {
		this._level = value;
	}

	get nextLevel(): string {
		return this._nextLevel;
	}

	set nextLevel(value: string) {
		this._nextLevel = value;
	}

	get parser(): string {
		return this._parser;
	}

	set parser(value: string) {
		this._parser = value;
	}

	get identifier(): string {
		return this._identifier;
	}

	set identifier(value: string) {
		this._identifier = value;
	}

	get url(): string {
		return this._url;
	}

	set url(value: string) {
		this._url = value;
	}

	get ratio(): number {
		return this._ratio;
	}

	set ratio(value: number) {
		this._ratio = value;
	}

	get noImage(): boolean {
		return this._noImage;
	}

	set noImage(value: boolean) {
		this._noImage = value;
	}

	get singleFile(): boolean {
		return this._singleFile;
	}

	set singleFile(value: boolean) {
		this._singleFile = value;
	}

	get imagesNo(): number {
		return this._imagesNo;
	}

	set imagesNo(value: number) {
		this._imagesNo = value;
	}

	get commentsNo(): number {
		return this._commentsNo;
	}

	set commentsNo(value: number) {
		this._commentsNo = value;
	}

	get thumbnails(): Array<string> {
		return this._thumbnails;
	}

	set thumbnails(value: Array<string>) {
		this._thumbnails = value;
	}

	get localThumbnails(): Array<string> {
		return this._localThumbnails;
	}

	set localThumbnails(value: Array<string>) {
		this._localThumbnails = value;
	}

	get statuses(): Array<any> {
		return this._statuses;
	}

	set statuses(value: Array<any>) {
		this._statuses = value;
	}

	get settings(): object {
		return this._settings;
	}

	set settings(value: object) {
		this._settings = value;
	}

	get queued(): boolean {
		return this._queued;
	}

	set queued(value: boolean) {
		this._queued = value;
	}

	get favorited(): boolean {
		return this._favorited;
	}

	set favorited(value: boolean) {
		this._favorited = value;
	}

	get finished(): boolean {
		return this._finished;
	}

	set finished(value: boolean) {
		this._finished = value;
	}

	get blocked(): boolean {
		return this._blocked;
	}

	set blocked(value: boolean) {
		this._blocked = value;
	}

	get lastViewedAt(): string {
		return this._lastViewedAt;
	}

	set lastViewedAt(value: string) {
		this._lastViewedAt = value;
	}

	get category(): Category {
		return this._category;
	}

	set category(value: Category) {
		this._category = value;
	}

	get tags(): Tag[] {
		return this._tags;
	}

	set tags(value: Tag[]) {
		this._tags = value;
	}

	/**
	 * Checks if node has specified tag
	 * @param tag
	 */
	public hasTag = (tag: Tag): boolean => {
		if (this._tags.length > 0) {
			for (let currentTag of this._tags) {
				if (tag.name === currentTag.name)
					return true;
			}
		}

		return false;
	};

	/**
	 * Adds tag to array
	 * @param tag
	 */
	public addTag = (tag: Tag): ParserNode => {
		if (!this.hasTag(tag))
			this._tags.push(tag);

		return this;
	};

	public removeTag = (tag: Tag): ParserNode => {
		if (this.hasTag(tag)) {
			let tagsList = this._tags;

			this._tags = [];

			for (let tagIndex in tagsList) {
				let checkedTag = tagsList[tagIndex];

				if (checkedTag.name !== tag.name)
					this._tags.push(checkedTag);
			}
		}

		return this;
	};

	/**
	 * Toggles status (add if not exists, remove if exists);
	 * @param status
	 */
	public toggleStatus = (status: string): ParserNode => {
		if (this.hasStatus(status))
			this.removeStatus(status);
		else
			this.addStatus(status);

		return this;
	};

	/**
	 * Adds status to library
	 *
	 * @param addedStatus
	 */
	private _addStatus = (addedStatus: string): ParserNode => {
		if (!this._hasStatus(addedStatus)) {
			this._statuses.push(addedStatus);
		}

		if (this[addedStatus] !== 'undefined')
			this[addedStatus] = true;

		return this;
	};

	/**
	 * Removes status from library
	 *
	 * @param removedStatus
	 */
	private _removeStatus = (removedStatus: string): ParserNode => {
		if (this._statuses.length > 0) {
			for (let index in this._statuses) {
				if (this._statuses[index] === removedStatus) {
					this._statuses.splice(parseInt(index), 1);
				}
			}
		}

		if (this[removedStatus] !== 'undefined')
			this[removedStatus] = false;

		return this;
	};

	/**
	 * Checks if status exists in library;
	 *
	 * @param checkedStatus
	 */
	private _hasStatus = (checkedStatus: string): boolean => {
		if (this._statuses.length > 0) {
			for (let statusKey in this._statuses) {
				if (this._statuses[statusKey] === checkedStatus) {
					return true;
				}
			}
		}

		return false;
	};

	get addStatus(): (addedStatus: string) => ParserNode {
		return this._addStatus;
	}

	set addStatus(value: (addedStatus: string) => ParserNode) {
		this._addStatus = value;
	}

	get removeStatus(): (removedStatus: string) => ParserNode {
		return this._removeStatus;
	}

	set removeStatus(value: (removedStatus: string) => ParserNode) {
		this._removeStatus = value;
	}

	get hasStatus(): (checkedStatus: string) => boolean {
		return this._hasStatus;
	}

	set hasStatus(value: (checkedStatus: string) => boolean) {
		this._hasStatus = value;
	}

	get allowTags(): boolean {
		return this._allowTags;
	}

	set allowTags(value: boolean) {
		this._allowTags = value;
	}

	get allowCategory(): boolean {
		return this._allowCategory;
	}

	set allowCategory(value: boolean) {
		this._allowCategory = value;
	}

	get customDescription(): string {
		return this._customDescription;
	}

	set customDescription(value: string) {
		this._customDescription = value;
	}
}