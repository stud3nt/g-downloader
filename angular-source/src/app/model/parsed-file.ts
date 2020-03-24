import { ParserNode } from "./parser-node";
import { BaseModel } from "./base/base-model";
import {Status} from "./status";

export class ParsedFile extends BaseModel {

	constructor(obj?: any) {
		super();

		Object.assign(this, obj);

		if (!obj.statuses || obj.statuses.length === 0)
			this._statuses = [];

		if (obj.status)
			this._status = new Status(obj.status);
	}

	private _name: string;

	private _title: string = null;

	private _description: string = null;

	private _mimeType: string = null;

	private _extension: string;

	private _parser: string;

	private _identifier: string;

	private _thumbnail: string = null;

	private _localThumbnail: string = null;

	private _url: string = null;

	private _localUrl: string = null;

	private _width: number = 0;

	private _height: number = 0;

	private _length: number = 0;

	private _size: number = 0;

	private _textSize: string;

	private _uploadedAt: string;

	private _ratio: number = -1;

	private _htmlPreview: string = null;

	// file type (image, movie)
	private _type: string;

	// specific file settings (renamer, folder etc);
	private _settings: object = {};

	// statuses library
	private _statuses = [];

	// request status
	private _status: Status = null;

	private _parentNode: ParserNode = null;

	/**
	 * Adds status to library
	 *
	 * @param addedStatus
	 */
	public addStatus(addedStatus: string): ParsedFile {
		if (addedStatus.length > 0 && !this.hasStatus(addedStatus)) {
			this._statuses.push(addedStatus);
		}

		return this;
	}

	/**
	 * Removes status from library
	 *
	 * @param removedStatus
	 */
	public removeStatus(removedStatus: string): ParsedFile {
		if (this._statuses && this._statuses.length > 0) {
			for (let statusKey in this._statuses) {
				let intStatusKey = parseInt(statusKey);

				if (this._statuses[statusKey] === removedStatus) {
					this._statuses.splice(intStatusKey, 1);
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
		if (this._statuses && this._statuses.length > 0) {
			for (let statusKey in this._statuses) {
				if (this._statuses[statusKey] === checkedStatus) {
					return true;
				}
			}
		}

		return false;
	}

	get name(): string {
		return this._name;
	}

	set name(value: string) {
		this._name = value;
	}

	get title(): string {
		return this._title;
	}

	set title(value: string) {
		this._title = value;
	}

	get description(): string {
		return this._description;
	}

	set description(value: string) {
		this._description = value;
	}

	get extension(): string {
		return this._extension;
	}

	set extension(value: string) {
		this._extension = value;
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

	get thumbnail(): string {
		return this._thumbnail;
	}

	set thumbnail(value: string) {
		this._thumbnail = value;
	}

	get localThumbnail(): string {
		return this._localThumbnail;
	}

	set localThumbnail(value: string) {
		this._localThumbnail = value;
	}

	get url(): string {
		return this._url;
	}

	set url(value: string) {
		this._url = value;
	}

	get localUrl(): string {
		return this._localUrl;
	}

	set localUrl(value: string) {
		this._localUrl = value;
	}

	get width(): number {
		return this._width;
	}

	set width(value: number) {
		this._width = value;
	}

	get height(): number {
		return this._height;
	}

	set height(value: number) {
		this._height = value;
	}

	get length(): number {
		return this._length;
	}

	set length(value: number) {
		this._length = value;
	}

	get size(): number {
		return this._size;
	}

	set size(value: number) {
		this._size = value;
	}

	get textSize(): string {
		return this._textSize;
	}

	set textSize(value: string) {
		this._textSize = value;
	}

	get uploadedAt(): string {
		return this._uploadedAt;
	}

	set uploadedAt(value: string) {
		this._uploadedAt = value;
	}

	get htmlPreview(): string {
		return this._htmlPreview;
	}

	set htmlPreview(value: string) {
		this._htmlPreview = value;
	}

	get type(): string {
		return this._type;
	}

	set type(value: string) {
		this._type = value;
	}

	get settings(): object {
		return this._settings;
	}

	set settings(value: object) {
		this._settings = value;
	}

	get statuses(): any[] {
		return this._statuses;
	}

	set statuses(value: any[]) {
		this._statuses = value;
	}

	get parentNode(): ParserNode {
		return this._parentNode;
	}

	set parentNode(value: ParserNode) {
		this._parentNode = value;
	}

	get mimeType(): string {
		return this._mimeType;
	}

	set mimeType(value: string) {
		this._mimeType = value;
	}

	get status(): Status {
		return this._status;
	}

	set status(value: Status) {
		this._status = value;
	}

	get ratio(): number {
		return this._ratio;
	}

	set ratio(value: number) {
		this._ratio = value;
	}
}