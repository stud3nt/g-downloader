import { BaseModel } from "./base/base-model";

export class ParserNodeSettings extends BaseModel {

	constructor(obj?: any) {
		super();

		Object.assign(this, obj);
	}

	private _id: number;

	private _prefixType: string = null;

	private _prefix: string = null;

	private _sufixType: string = null;

	private _sufix: string = null;

	private _folderType: string = null;

	private _folder: string = null;

	private _maxSize: number = 0;

	private _sizeUnit: string = null;

	private _maxWidth: number = 0;

	private _maxHeight: number = 0;

	private _minLength: number = 0;

    get id(): number {
        return this._id;
    }

    set id(value: number) {
        this._id = value;
    }

    get prefix(): string {
        return this._prefix;
    }

    set prefix(value: string) {
        this._prefix = value;
    }

    get sufix(): string {
        return this._sufix;
    }

    set sufix(value: string) {
        this._sufix = value;
    }

    get folder(): string {
        return this._folder;
    }

    set folder(value: string) {
        this._folder = value;
    }

    get maxSize(): number {
        return this._maxSize;
    }

    set maxSize(value: number) {
        this._maxSize = value;
    }

    get maxWidth(): number {
        return this._maxWidth;
    }

    set maxWidth(value: number) {
        this._maxWidth = value;
    }

    get maxHeight(): number {
        return this._maxHeight;
    }

    set maxHeight(value: number) {
        this._maxHeight = value;
    }

    get prefixType(): string {
        return this._prefixType;
    }

    set prefixType(value: string) {
        this._prefixType = value;
    }

    get sufixType(): string {
        return this._sufixType;
    }

    set sufixType(value: string) {
        this._sufixType = value;
    }

    get folderType(): string {
        return this._folderType;
    }

    set folderType(value: string) {
        this._folderType = value;
    }

    get sizeUnit(): string {
        return this._sizeUnit;
    }

    set sizeUnit(value: string) {
        this._sizeUnit = value;
    }

    get minLength(): number {
        return this._minLength;
    }

    set minLength(value: number) {
        this._minLength = value;
    }
}
